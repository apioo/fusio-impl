<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\Plan;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Event\Plan\PayedEvent;
use Fusio\Impl\Event\PlanEvents;
use Fusio\Impl\Service\Plan\Model\Product;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Payment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Payment
{
    protected $providers;
    
    protected $planTable;
    
    public function __construct(Table\Plan $planTable)
    {
        $this->planTable = $planTable;
    }

    public function addProvider($name, ProviderInterface $provider)
    {
        $this->providers[$name] = $provider;
    }

    public function prepare($name, $planId)
    {
        $provider = $this->getProvider($name);
        $product  = $this->createProduct($planId);

        $returnUrl   = '';
        $cancelUrl   = '';
        $approvalUrl = $provider->prepare($product, $returnUrl, $cancelUrl);

        return [
            'approvalUrl' => $approvalUrl,
        ];
    }

    public function execute()
    {
        
    }

    /**
     * @param string $name
     * @return \Fusio\Impl\Service\Plan\ProviderInterface
     */
    private function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new StatusCode\BadRequestException('Invalid payment provider');
        }

        return $this->providers[$name];
    }

    /**
     * @param integer $planId
     * @return \Fusio\Impl\Service\Plan\Model\Product
     */
    private function createProduct($planId)
    {
        $plan = $this->planTable->get($planId);

        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        if ($plan['status'] != Table\Plan::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        $product = new Product();
        $product->setId($plan['id']);
        $product->setName($plan['name']);
        $product->setPrice(floatval($plan['price']));

        return $product;
    }
}
