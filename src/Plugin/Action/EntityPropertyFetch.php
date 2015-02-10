<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntityPropertyFetch.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Engine\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Fetch entities by property' action.
 *
 * @Action(
 *   id = "rules_entity_property_fetch",
 *   label = @Translation("Fetch entities by property"),
 *   category = @Translation("Entity"),
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
 *       label = @Translation("Value"),
 *       description = @Translation("The property value of the entity to be fetched."),
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       description = @Translation("Limit the maximum number of fetched entities."),
 *       required = FALSE,
 *     ),
 *   },
 *   provides = {
 *      "entity_fetched" = @ContextDefinition("list",
 *        label = @Translation("Fetched entity"),
 *      )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityPropertyFetch extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityPropertyFetch object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

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
    // Retrieve context values for action.
    $entity_type = $this->getContextValue('type');
    $entity_property = $this->getContextValue('property');
    $property_value = $this->getContextValue('value');
    $limit = $this->getContextValue('limit');

    $storage = $this->entityManager->getStorage($entity_type);

    // When retrieving entities, if $limit is not set there is no need to use query object directly.
    $entities = [];
    if (is_null($limit)) {
      $entities = $storage->loadByProperties(array($entity_property => $property_value));
    }
    else {
      $query = $storage->getQuery();
      $entity_ids = $query
        ->condition($entity_property, $property_value, '=')
        ->range(0, $limit)
        ->execute();
      $entities = $storage->loadMultiple($entity_ids);
    }

    // Set provided value.
    $this->setProvidedValue('entity_fetched', $entities);
  }

}
