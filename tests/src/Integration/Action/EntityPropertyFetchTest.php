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

  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Prepare dummy entity manager.
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
   * Tests the action execution.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithNoLimit() {
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';

    // @todo add comments
    $entities = array();
    for($i=0; $i < 2; $i++) {
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }

    $entityStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entityStorage->expects($this->once())
        ->method('loadByProperties')
        ->with(array($property_name => $property_value))
        ->will($this->returnValue($entities));
    $this->entityManager->expects($this->once())
        ->method('getStorage')
        ->with($entity_type)
        ->will($this->returnValue($entityStorage));

    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('property', $property_name)
      ->setContextValue('value', $property_value)
      ->execute();

    $this->assertEquals($entities, $this->action->getProvided('entity_fetched')->getContextValue('entity_fetched'));
  }

  /**
   * Tests the action execution.
   *
   * @covers ::execute()
   */
  public function testActionExecutionWithLimit() {
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';
    $limit = 2;

    // @todo add comments.
    $entities = array();
    for($i=0; $i < 4; $i++) {
      $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
      $entities[] = $entity;
    }
    $entities = array_slice($entities, 0, $limit);
    $entity_ids = range(1, $limit);

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


    $this->action->setContextValue('type', $entity_type)
        ->setContextValue('property', $property_name)
        ->setContextValue('value', $property_value)
        ->setContextValue('limit', $limit)
        ->execute();

    $this->assertEquals($entities, $this->action->getProvided('entity_fetched')->getContextValue('entity_fetched'));
  }

}
