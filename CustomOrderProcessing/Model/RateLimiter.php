<?php
namespace Vendor\CustomOrderProcessing\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Exception\LocalizedException;

class RateLimiter
{
    /**
     * Cache type for rate limiting
     */
    const CACHE_TYPE = 'config';

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;

    /**
     * @var int
     */
    private $maxRequests;

    /**
     * @var int
     */
    private $timeWindow;

    /**
     * @param Pool $cacheFrontendPool
     * @param int $maxRequests
     * @param int $timeWindow
     */
    public function __construct(
        Pool $cacheFrontendPool,
        int $maxRequests = 100,
        int $timeWindow = 3600
    ) {
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    /**
     * Check if request is allowed based on rate limiting
     *
     * @param string $identifier
     * @return bool
     * @throws LocalizedException
     */
    public function isAllowed(string $identifier): bool
    {
        $cache = $this->cacheFrontendPool->get(self::CACHE_TYPE);
        $cacheKey = 'rate_limit_' . md5($identifier);
        
        $data = $cache->load($cacheKey);
        $now = time();
        
        if ($data) {
            $data = json_decode($data, true);
            
            // Clean up old timestamps
            $data['timestamps'] = array_filter($data['timestamps'], function($timestamp) use ($now) {
                return $timestamp > ($now - $this->timeWindow);
            });
            
            // Check if rate limit is exceeded
            if (count($data['timestamps']) >= $this->maxRequests) {
                throw new LocalizedException(
                    __('Rate limit exceeded. Please try again later.')
                );
            }
            
            // Add current timestamp
            $data['timestamps'][] = $now;
        } else {
            $data = [
                'timestamps' => [$now]
            ];
        }
        
        // Save updated data
        $cache->save(
            json_encode($data),
            $cacheKey,
            [],
            $this->timeWindow
        );
        
        return true;
    }
}