<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Statistic_Chart implements \JsonSerializable
{
    /**
     * @var array<string>|null
     */
    protected $labels;
    /**
     * @var array<Statistic_Chart_Data>|null
     */
    protected $data;
    /**
     * @var array<string>|null
     */
    protected $series;
    /**
     * @param array<string>|null $labels
     */
    public function setLabels(?array $labels) : void
    {
        $this->labels = $labels;
    }
    /**
     * @return array<string>|null
     */
    public function getLabels() : ?array
    {
        return $this->labels;
    }
    /**
     * @param array<Statistic_Chart_Data>|null $data
     */
    public function setData(?array $data) : void
    {
        $this->data = $data;
    }
    /**
     * @return array<Statistic_Chart_Data>|null
     */
    public function getData() : ?array
    {
        return $this->data;
    }
    /**
     * @param array<string>|null $series
     */
    public function setSeries(?array $series) : void
    {
        $this->series = $series;
    }
    /**
     * @return array<string>|null
     */
    public function getSeries() : ?array
    {
        return $this->series;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('labels' => $this->labels, 'data' => $this->data, 'series' => $this->series), static function ($value) : bool {
            return $value !== null;
        });
    }
}
