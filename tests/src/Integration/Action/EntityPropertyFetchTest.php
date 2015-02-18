<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityPropertyFetchTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\EntityPropertyFetch
 * @group rules_action
 */
class EntityPropertyFetchTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Prepare our own dummy entityManager as the entityManager in
    // RulesEntityIntegrationTestBase does not mock the getStorage method.
    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManager')
      ->setMethods(['getBundleInfo', 'getStorage'])
      ->setConstructorArgs([
        $this->namespaces,
        $this->moduleHandler,
        $this->cacheBackend,
        $this->getMock('Drupal\Core\Language\LanguageManagerInterface'),
        $this->getStringTranslationStub(),
        $this->getClassResolverStub(),
        $this->typedDataManager,
        $this->getMock('Drupal\Core\KeyValueStore\KeyValueStoreInterface'),
        $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
      ])
      ->getMock();

    // Return some dummy bundle information for now, so that the entity manager
    // does not call out to the config entity system to get bundle information.
    $this->entityManager->expects($this->any())
      ->method('getBundleInfo')
      ->with($this->anything())
      ->willReturn(['entity_test' => ['label' => 'Entity Test']]);
    $this->container->set('entity.manager', $this->entityManager);

    $this->action = $this->actionManager->createInstance('rules_entity_property_fetch');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Fetch entities by property', $this->action->summary());
  }

  /**
   * Tests action execution when no value for limit is provided.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithNoLimit() {
    // Create variables for action context values.
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';

    // Create an array of dummy entities.
    $entities = [];
    for ($i = 0; $i < 2; $i++) {
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(array($property_name => $property_value))
      ->will($this->returnValue($entities));
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->will($this->returnValue($entityStorage));

    // Set context values for EntityPropertyFetch action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('property', $property_name)
      ->setContextValue('value', $property_value)
      ->execute();

    // Test that executing action without a value for limit returns the dummy entities array.
    $this->assertEquals($entities, $this->action->getProvided('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests action execution when a value for limit is provided.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithLimit() {
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';
    $limit = 2;

    // Create an array of dummy entities.
    $entities = array();
    for ($i = 0; $i < 4; $i++) {
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    // Create new dummy array of entities for testing limit.
    $entities = array_slice($entities, 0, $limit);

    // Creates entity ids for new dummy array of entities.
    $entity_ids = range(1, $limit);

    // Create dummy query object.
    $query = $this->getMock('Drupal\Core\Entity\Query\QueryInterface');
    $query->expects($this->once())
      ->method('condition')
      ->with($property_name, $property_value, '=')
      ->will($this->returnValue($query));
    $query->expects($this->once())
      ->method('range')
      ->with(0, $limit)
      ->will($this->returnValue($query));
    $query->expects($this->once())
      ->method('execute')
      ->will($this->returnValue($entity_ids));

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadMultiple')
      ->with($entity_ids)
      ->will($this->returnValue($entities));
    $entityStorage->expects($this->once())
      ->method('getQuery')
      ->will($this->returnValue($query));
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->will($this->returnValue($entityStorage));


    // Set context values for EntityPropertyFetch action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('property', $property_name)
      ->setContextValue('value', $property_value)
      ->setContextValue('limit', $limit)
      ->execute();

    // Test that executing action with a value for limit returns the dummy entities array.
    $this->assertEquals($entities, $this->action->getProvided('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests that the context provided by the action execution has the correct entity type.
   *
   * @covers ::execute()
   */
  function testActionExecutionProvidedContextEntityType() {
    // Create variables for action context values.
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';

    // Create an array of dummy entities.
    $entities = [];
    for ($i = 0; $i < 2; $i++) {
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    // Create dummy entity storage object.
    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(array($property_name => $property_value))
      ->will($this->returnValue($entities));
    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with($entity_type)
      ->will($this->returnValue($entityStorage));

    // Set context values for EntityPropertyFetch action and execute.
    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('property', $property_name)
      ->setContextValue('value', $property_value)
      ->execute();

    // Test that the provided context has the correct entity type.
    $this->assertEquals('entity:' . $entity_type, $this->action->getProvidedDefinition('entity_fetched')->getDataType());
  }

}