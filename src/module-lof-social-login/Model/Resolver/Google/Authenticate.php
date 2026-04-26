<?php
namespace Lof\SocialLogin\Model\Resolver\Google;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

use Lof\SocialLogin\Model\Google;
use Lof\SocialLogin\Helper\Google\Data as GoogleHelper;
use Lof\SocialLogin\Model\ResourceModel\Social\CollectionFactory as SocialCollectionFactory;
use Lof\SocialLogin\Model\SocialFactory;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;

class Authenticate implements ResolverInterface
{
    protected $helper;
    protected $socialCollectionFactory;
    protected $socialFactory;
    protected $customerRepo;
    protected $customerFactory;
    protected $accountManagement;
    protected $tokenModelFactory;
    protected $google;

    public function __construct(
        GoogleHelper $helper,
        SocialCollectionFactory $socialCollectionFactory,
        SocialFactory $socialFactory,
        CustomerRepositoryInterface $customerRepo,
        CustomerFactory $customerFactory,
        Google $google,
        TokenModelFactory $tokenFactory,
        AccountManagementInterface $accountManagement
    ) {
        $this->helper = $helper;
        $this->socialCollectionFactory = $socialCollectionFactory;
        $this->socialFactory = $socialFactory;
        $this->customerRepo = $customerRepo;
        $this->customerFactory = $customerFactory;
        $this->google = $google;
        $this->tokenModelFactory = $tokenFactory;
        $this->accountManagement = $accountManagement;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['jwt_token'])) {
            throw new GraphQlInputException(__('Missing Google JWT Token.'));
        }

        /** Step 1: Google code > token */
        // $tokenResponse = $this->exchangeCode($args['code']);

        // if (empty($tokenResponse['id_token'])) {
        //     throw new GraphQlAuthenticationException(__('Google authentication failed.'));
        // }

        /** Step 2: Decode Google user profile */
        $profile = $this->decodeJwt($args['jwt_token']);

        if (empty($profile['sub'])) {
            throw new GraphQlAuthenticationException(__('Invalid Google profile.'));
        }
        // dd($profile);
        $googleId  = $profile['sub'];
        $email     = $profile['email'] ?? null;
        $firstname = $profile['given_name'] ?? 'Google';
        $lastname  = $profile['family_name'] ?? 'User';
        $picture   = $profile['picture'] ?? '';

        if (!$email) {
            throw new GraphQlAuthenticationException(__('Google account has no email.'));
        }

        /** Step 3: Check existing mapping */
        $collection = $this->socialCollectionFactory->create();
        $mapping = $collection
            ->addFieldToFilter('social_id', $googleId)
            ->addFieldToFilter('type', 'google')
            ->getFirstItem();

        /** If mapping exists > return token */
        if ($mapping->getId()) {
            $customer = $this->customerRepo->getById($mapping->getCustomerId());
            return ['token' => $this->generateToken($customer->getId())];
        }

        /** Step 4: Check customer by email */
        $customer = $this->helper->getCustomerByEmail($email);

        if (!$customer || !$customer->getId()) {

            /** Prepare customer data like original controller */
            $password = $this->helper->createPassword();

            $data = [
                'id'                    => $googleId,
                'email'                 => $email,
                'password'              => $password,
                'password_confirmation' => $password,
                'first_name'            => $firstname,
                'last_name'             => $lastname,
                'picture'               => $picture
            ];

            $store = $context->getExtensionAttributes()->getStore();
            $storeId = $store->getId();

            $websiteId = $store->getWebsite()->getId();

            /** Create customer */
            $customer = $this->helper->createCustomerMultiWebsite($data, $websiteId, $storeId);

            /** Send password email if enabled */
            if ($this->helper->sendPassword()) {
                try {
                    $this->accountManagement->sendPasswordReminderEmail($customer);
                } catch (\Exception $e) {
                    // fail silently (same behavior as original controller)
                }
            }
        }

        /** Step 5: Save Google > Magento mapping */
        $social = $this->socialFactory->create();
        $social->setData([
            'social_id'   => $googleId,
            'username'    => $googleId,
            'customer_id' => $customer->getId(),
            'type'        => 'google',
            'picture'     => $picture
        ]);
        $social->save();

        /** Step 6: Return customer token */
        return ['token' => $this->generateToken($customer->getId())];
    }

    /** Generate token (no password needed) */
    protected function generateToken($customerId)
    {
        return $this->tokenModelFactory
            ->create()
            ->createCustomerToken($customerId)
            ->getToken();
    }

    /** Exchange Google code > token */
    protected function exchangeCode($code)
    {
        $params = [
            'code'          => urldecode($code),
            'client_id'     => $this->helper->getClientId(),
            'client_secret' => $this->helper->getClientSecret(),
            'redirect_uri'  => $this->google->getBaseUrl(),
            'grant_type'    => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return json_decode(curl_exec($ch), true);
    }

    /** Decode Google JWT */
    protected function decodeJwt($jwt)
    {
        list($header, $payload, $signature) = explode('.', $jwt);
        return json_decode(
            base64_decode(strtr($payload, '-_', '+/')),
            true
        );
    }
}
