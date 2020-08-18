<?php

/**
* This file is part of the ecom/kassa-sdk library
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Ecom\KassaSdk;

class QueueManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }


	/**
	 * Sends a check to queue
	 *
	 * @param Check $check Check instance
	 * @param $operationName
	 *
	 * @return mixed
	 */
    public function putCheck(Check $check, $operationName)
    {
//        if ($queueName === null) {
//            if ($this->defaultQueue === null) {
//                throw new \LogicException('Default queue is not set');
//            }
//            $queueName = $this->defaultQueue;
//        }
//
//        if (!$this->hasQueue($queueName)) {
//            throw new \InvalidArgumentException(sprintf('Unknown queue "%s"', $queueName));
//        }

	    // POST /fiscalorder/getToken
	    // POST /fiscalorder/:storeID/:operation?tokenid=:tokenid
	    // GET  /fiscalorder/:storeID/report/:issueID?tokenid=:tokenid

	    $storeID = get_option('ecomkassa_shop_id');

        $path = sprintf('/%d/%s', $storeID , $operationName);

	    return $this->client->sendRequest($path, $check->asArray());
    }

    /**
     * Whether queue active
     *
     * @param string $name Queue name
     *
     * @return bool
     */
    public function isQueueActive($name)
    {
        if (!$this->hasQueue($name)) {
            throw new \InvalidArgumentException(sprintf('Unknown queue "%s"', $name));
        }
        $path = sprintf('api/shop/v1/queues/%s', $this->queues[$name]);
        $data = $this->client->sendRequest($path);
        return is_array($data) && array_key_exists('state', $data) ? $data['state'] == 'active' : false;
    }
}
