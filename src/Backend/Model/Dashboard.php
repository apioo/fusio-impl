<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Dashboard implements \JsonSerializable
{
    /**
     * @var Statistic_Chart|null
     */
    protected $errorsPerRoute;
    /**
     * @var Statistic_Chart|null
     */
    protected $incomingRequests;
    /**
     * @var Statistic_Chart|null
     */
    protected $incomingTransactions;
    /**
     * @var Statistic_Chart|null
     */
    protected $mostUsedRoutes;
    /**
     * @var Statistic_Chart|null
     */
    protected $timePerRoute;
    /**
     * @var Dashboard_Apps|null
     */
    protected $latestApps;
    /**
     * @var Dashboard_Requests|null
     */
    protected $latestRequests;
    /**
     * @var Dashboard_Users|null
     */
    protected $latestUsers;
    /**
     * @var Dashboard_Transactions|null
     */
    protected $latestTransactions;
    /**
     * @param Statistic_Chart|null $errorsPerRoute
     */
    public function setErrorsPerRoute(?Statistic_Chart $errorsPerRoute) : void
    {
        $this->errorsPerRoute = $errorsPerRoute;
    }
    /**
     * @return Statistic_Chart|null
     */
    public function getErrorsPerRoute() : ?Statistic_Chart
    {
        return $this->errorsPerRoute;
    }
    /**
     * @param Statistic_Chart|null $incomingRequests
     */
    public function setIncomingRequests(?Statistic_Chart $incomingRequests) : void
    {
        $this->incomingRequests = $incomingRequests;
    }
    /**
     * @return Statistic_Chart|null
     */
    public function getIncomingRequests() : ?Statistic_Chart
    {
        return $this->incomingRequests;
    }
    /**
     * @param Statistic_Chart|null $incomingTransactions
     */
    public function setIncomingTransactions(?Statistic_Chart $incomingTransactions) : void
    {
        $this->incomingTransactions = $incomingTransactions;
    }
    /**
     * @return Statistic_Chart|null
     */
    public function getIncomingTransactions() : ?Statistic_Chart
    {
        return $this->incomingTransactions;
    }
    /**
     * @param Statistic_Chart|null $mostUsedRoutes
     */
    public function setMostUsedRoutes(?Statistic_Chart $mostUsedRoutes) : void
    {
        $this->mostUsedRoutes = $mostUsedRoutes;
    }
    /**
     * @return Statistic_Chart|null
     */
    public function getMostUsedRoutes() : ?Statistic_Chart
    {
        return $this->mostUsedRoutes;
    }
    /**
     * @param Statistic_Chart|null $timePerRoute
     */
    public function setTimePerRoute(?Statistic_Chart $timePerRoute) : void
    {
        $this->timePerRoute = $timePerRoute;
    }
    /**
     * @return Statistic_Chart|null
     */
    public function getTimePerRoute() : ?Statistic_Chart
    {
        return $this->timePerRoute;
    }
    /**
     * @param Dashboard_Apps|null $latestApps
     */
    public function setLatestApps(?Dashboard_Apps $latestApps) : void
    {
        $this->latestApps = $latestApps;
    }
    /**
     * @return Dashboard_Apps|null
     */
    public function getLatestApps() : ?Dashboard_Apps
    {
        return $this->latestApps;
    }
    /**
     * @param Dashboard_Requests|null $latestRequests
     */
    public function setLatestRequests(?Dashboard_Requests $latestRequests) : void
    {
        $this->latestRequests = $latestRequests;
    }
    /**
     * @return Dashboard_Requests|null
     */
    public function getLatestRequests() : ?Dashboard_Requests
    {
        return $this->latestRequests;
    }
    /**
     * @param Dashboard_Users|null $latestUsers
     */
    public function setLatestUsers(?Dashboard_Users $latestUsers) : void
    {
        $this->latestUsers = $latestUsers;
    }
    /**
     * @return Dashboard_Users|null
     */
    public function getLatestUsers() : ?Dashboard_Users
    {
        return $this->latestUsers;
    }
    /**
     * @param Dashboard_Transactions|null $latestTransactions
     */
    public function setLatestTransactions(?Dashboard_Transactions $latestTransactions) : void
    {
        $this->latestTransactions = $latestTransactions;
    }
    /**
     * @return Dashboard_Transactions|null
     */
    public function getLatestTransactions() : ?Dashboard_Transactions
    {
        return $this->latestTransactions;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('errorsPerRoute' => $this->errorsPerRoute, 'incomingRequests' => $this->incomingRequests, 'incomingTransactions' => $this->incomingTransactions, 'mostUsedRoutes' => $this->mostUsedRoutes, 'timePerRoute' => $this->timePerRoute, 'latestApps' => $this->latestApps, 'latestRequests' => $this->latestRequests, 'latestUsers' => $this->latestUsers, 'latestTransactions' => $this->latestTransactions), static function ($value) : bool {
            return $value !== null;
        });
    }
}
