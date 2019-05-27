<?php
/**
 * cakephp-instagram (https://github.com/smartsolutionsitaly/cakephp-instagram)
 * Copyright (c) 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 *
 * Instagram client for CakePHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @category  cakephp-plugin
 * @package   cakephp-instagram
 * @author    Lucio Benini <dev@smartsolutions.it>
 * @copyright 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://smartsolutions.it Smart Solutions
 * @since     1.0.0
 */

namespace SmartSolutionsItaly\CakePHP\Instagram\Model\Behavior;

use Cake\Collection\CollectionInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use SmartSolutionsItaly\CakePHP\Instagram\Http\Client\InstagramClient;

/**
 * Instagram behavior.
 * @package SmartSolutionsItaly\CakePHP\Instagram\Model\Behavior
 * @author Lucio Benini
 * @since 1.0.0
 */
class InstagramBehavior extends Behavior
{
    /**
     * Default configuration.
     * @var array
     */
    protected $_defaultConfig = [
        'count' => 9,
        'field' => 'instagram'
    ];

    /**
     * Finder for Instagram images.
     * Adds a formatter to the and replaces the field containing token with the images.
     * @param Query $query The query object.
     * @param array $options Query options. May contains "count", "format" and "field" elements.
     * @return Query The query object.
     */
    public function findInstagram(Query $query, array $options)
    {
        $options = $options + [
                'count' => (int)$this->getConfig('count'),
                'field' => (string)$this->getConfig('field'),
                'format' => true
            ];

        return $query
            ->formatResults(function (CollectionInterface $results) use ($options) {
                $client = new InstagramClient;

                return $results->map(function ($row) use ($options, $client) {
                    if (!empty($row[$options['field']]) && !is_array($row[$options['field']])) {
                        if ($options['format']) {
                            $row[$options['field']] = $client->getImages((string)$row[$options['field']], (int)$options['count']);
                        } else {
                            $row[$options['field']] = $client->getMedia((string)$row[$options['field']], (int)$options['count']);
                        }
                    }

                    return $row;
                });
            }, Query::APPEND);
    }
}
