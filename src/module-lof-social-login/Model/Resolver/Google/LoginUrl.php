<?php
namespace Lof\SocialLogin\Model\Resolver\Google;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\SocialLogin\Helper\Google\Data as GoogleHelper;

class LoginUrl implements ResolverInterface
{
    protected $helper;

    public function __construct(GoogleHelper $helper)
    {
        $this->helper = $helper;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $clientId = $this->helper->getClientId();
        $redirectUri = $this->helper->getAuthUrl(); // this exists in your helper
        // dd($redirectUri);

        $url =
            'https://accounts.google.com/o/oauth2/auth?' .
            http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'email profile'
            ]);

        return [
            'url' => $url,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri
        ];
    }
}
