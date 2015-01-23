<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntityPropertyFetch.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Fetch entities by property' action.
 *
 * @Action(
 *   id = "rules_entity_property_fetch_action",
 *   label = @Translation("Fetch entities by property"),
 *   category = @Translation("Data"),
 *   context = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type"),
 *       description = @Translation("Specifies the type of the entity that should be fetched."),
 *     ),
 *     "property" = @ContextDefinition("string",
 *       label = @Translation("Property"),
 *       description = @Translation("The property by which the entity is to be selected.."),
 *     ),
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Vakye"),
 *       description = @Translation("The property value of the entity to be fetched."),
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       description = @Translation("Limit the maximum number of fetched entities."),
 *     ),
 *   },
 *   provides = {
 *      "entity_fetched" = @ContextDefinition("list",
 *        label = @Translation("Fetched entity"),
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityPropertyFetch extends RulesActionBase {

    /**
     * {@inheritdoc}
     */
    public function summary() {
        return $this->t('Fetch entities by property');
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $entity_type = $this->getContextValue('type');
        $entity_property = $this->getContextValue('property');
        $property_value = $this->getContextValue('value');
        $limit = $this->getContextValue('limit');

        $fetched_entity = entity_load_multiple_by_properties($entity_type, $entity_property, $property_value);
        $this->setContextValue('fetched_entity', $fetched_entity);
    }

}
