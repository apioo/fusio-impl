<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Model\Backend\StatisticChart;
use Fusio\Model\Backend\StatisticChartSeries;
use PSX\Sql\ViewAbstract;

/**
 * ChartViewAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class ChartViewAbstract extends ViewAbstract
{
    protected function build(array $data, array $seriesNames, array $labels): StatisticChart
    {
        $allSeries = [];
        foreach ($seriesNames as $key => $name) {
            $series = new StatisticChartSeries();
            $series->setName($name);
            $series->setData(array_values($data[$key] ?? []));
            $allSeries[] = $series;
        }

        $chart = new StatisticChart();
        $chart->setLabels($labels);
        $chart->setSeries($allSeries);
        return $chart;
    }
}
