<?php
/**
 * @category    Gateway Payment
 * @package     Ifthenpay_Payment
 * @author      Ifthenpay
 * @copyright   Ifthenpay (https://www.ifthenpay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Ifthenpay\Multibanco\Helper;


use Magento\Framework\HTTP\Client\Curl;


class HttpClient
{
    private $httpClient;

    public function __construct(Curl $httpClient)
    {
        $this->httpClient = $httpClient;
    }



    public function doPost(string $url, array $data, bool $isJsonContent = true): void
    {
        if ($isJsonContent) {
            $this->httpClient->addHeader("Content-Type", "application/json");
            $requestBody = json_encode($data);
        } else {
            $requestBody = $data;
        }

        try {
            $this->httpClient->post(
                $url,
                $requestBody
            );
        } catch (\Throwable $th) {

            if ($this->httpClient->getStatus() === 500) {
                throw new \Exception("Request failed on server side.");
            } else {
                throw new \Exception("Request failed.");
            }
        }
    }


    public function doGet(string $url, array $data = [], bool $isJsonContent = true): void
    {
        $arguments = !empty($data) ? '?' . http_build_query($data) : '';
        $url .= $arguments;

        try {
            $this->httpClient->get($url);
        } catch (\Throwable $th) {
            if ($this->httpClient->getStatus() === 500) {
                throw new \Exception("Request failed on server side.");
            } else {
                throw new \Exception("Request failed.");
            }
        }
    }




    public function getBody(): string
    {
        return $this->httpClient->getBody();
    }

    public function getBodyArray(): array
    {
        return json_decode($this->httpClient->getBody(), true);
    }

    public function getStatus(): int
    {
        return $this->httpClient->getStatus();
    }
}
