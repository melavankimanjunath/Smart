<?php
namespace Vendor\CustomOrderProcessing\Plugin\Rest;

use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;

class SecurityHeadersPlugin
{
    /**
     * Add security headers to REST API responses
     *
     * @param Rest $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return ResponseInterface
     */
    public function aroundDispatch(
        Rest $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        /** @var ResponseInterface $response */
        $response = $proceed($request);
        
        // Only add headers if this is our custom endpoint
        if (strpos($request->getPathInfo(), '/V1/orders/update-status') !== false) {
            // Add security headers
            $response->setHeader('X-Content-Type-Options', 'nosniff', true);
            $response->setHeader('X-XSS-Protection', '1; mode=block', true);
            $response->setHeader('X-Frame-Options', 'DENY', true);
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', true);
            $response->setHeader('Content-Security-Policy', "default-src 'self'", true);
            $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin', true);
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            $response->setHeader('Pragma', 'no-cache', true);
        }
        
        return $response;
    }
}