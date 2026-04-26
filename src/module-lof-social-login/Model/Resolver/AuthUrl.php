<?php
namespace Lof\SocialLogin\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

class AuthUrl implements ResolverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Resolve GraphQL query to return authUrl, callbackUrl and redirectPage for a provider
     *
     * provider example: "google", "facebook"
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array $args
     * @return array
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['provider']) || empty($args['provider'])) {
            throw new LocalizedException(__('Provider argument is required.'));
        }

        $provider = strtolower($args['provider']);
        $ucProvider = ucfirst($provider);

        // Provider helper class, e.g. Lof\SocialLogin\Helper\Google\Data
        $providerHelperClass = "Lof\\SocialLogin\\Helper\\{$ucProvider}\\Data";

        if (!class_exists($providerHelperClass)) {
            throw new LocalizedException(__("Provider helper not found: %1", $providerHelperClass));
        }

        /** @var object $providerHelper */
        $providerHelper = $this->objectManager->create($providerHelperClass);

        // callbackUrl (redirect_uri) - required by provider
        if (!method_exists($providerHelper, 'getAuthUrl')) {
            throw new LocalizedException(__("Provider helper %1 does not have getAuthUrl()", $providerHelperClass));
        }
        $callbackUrl = $providerHelper->getAuthUrl();

        // authUrl - prefer helper::getLoginUrl() if available, otherwise fallback to module route:
        $authUrl = '';
        if (method_exists($providerHelper, 'getLoginUrl')) {
            // If your helper happens to expose a provider-specific login URL, use it.
            $authUrl = $providerHelper->getLoginUrl();
        }

        // Fallback - use helper->getUrl to build a module entrypoint for starting auth (common pattern)
        if (empty($authUrl)) {
            if (method_exists($providerHelper, 'getUrl')) {
                // e.g. will generate: <baseUrl>/lofsociallogin/google/login
                $authUrl = rtrim($providerHelper->getUrl("lofsociallogin/{$provider}/login"), '/');
            } else {
                // Last resort: build using UrlInterface
                $url = $this->objectManager->get(\Magento\Framework\UrlInterface::class);
                $authUrl = rtrim($url->getUrl("lofsociallogin/{$provider}/login"), '/');
            }
        }

        // read redirect page from main module helper config (same config the controller uses)
        $mainHelperClass = 'Lof\\SocialLogin\\Helper\\Data';
        $redirectPage = '';
        if (class_exists($mainHelperClass)) {
            $mainHelper = $this->objectManager->create($mainHelperClass);
            if (method_exists($mainHelper, 'getConfig')) {
                // prefer getConfig if available
                try {
                    $redirectPage = $mainHelper->getConfig('sociallogin/general/redirect_page');
                } catch (\Throwable $t) {
                    $redirectPage = '';
                }
            } elseif (method_exists($mainHelper, 'getConfigValue')) {
                try {
                    $redirectPage = $mainHelper->getConfigValue('sociallogin/general/redirect_page');
                } catch (\Throwable $t) {
                    $redirectPage = '';
                }
            } elseif (method_exists($mainHelper, 'getConfigValue')) {
                // duplicate check, but safe
                $redirectPage = $mainHelper->getConfigValue('sociallogin/general/redirect_page');
            } else {
                // attempt the previously used getConfigValue signature fallback:
                try {
                    $redirectPage = $mainHelper->getConfigValue('sociallogin/general/redirect_page');
                } catch (\Throwable $t) {
                    $redirectPage = '';
                }
            }
        }

        return [
            'authUrl'      => $authUrl,
            'callbackUrl'  => $callbackUrl,
            'redirectPage' => $redirectPage
        ];
    }
}
