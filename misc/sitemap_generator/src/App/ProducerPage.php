<?php
namespace Seiya\App;

define('BASE_URL', 'http://anyway-grapes.jp/producers');

class ProducerPage
{
    private $producer;

    /**
     * Constructor
     *
     * @param associative array $producer
     *    Target producer
     */
    public function __construct(array $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Get page URL of the producer.
     *
     */
    public function getUrl()
    {
        $url = '';

        if (!empty($this->producer))
        {
            $url = BASE_URL;

            if (!empty($this->producer['country'])) {
                $country  = $this->producer['country'];
                $lang     = ($country === 'France' ? 'fr' : 'ja');
                $urlifier = new \Vendor\Seiya\Url\Urlifier($lang);

                $url_tokens = [ $urlifier->convert($country) ];
                $url_tokens[] = !empty($this->producer['region']) ? $urlifier->convert($this->producer['region']) : '';
                $url_tokens[] = (!empty($this->producer['district']) && $lang === 'fr') ? $urlifier->convert($this->producer['district']) : '';
                $url_tokens[] = !empty($this->producer['village']) ? $urlifier->convert($this->producer['village']) : '';
                $url_tokens[] = !empty($this->producer['short_name']) ? $urlifier->convert($this->producer['short_name']) : '';

                foreach ($url_tokens as $url_token)
                {
                    $url .= ('/' . $url_token);
                }
            }
        }

        return $url;
    }
}

