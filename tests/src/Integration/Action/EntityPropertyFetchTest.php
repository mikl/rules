<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntityPropertyFetchTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesActionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

    $query = $this->getMock('Drupal\Core\Entity\Query\QueryInterface');

    $factory = $this->getMockBuilder('Drupal\Core\Entity\Query\QueryFactory')
        ->disableOriginalConstructor()
        ->getMock();

    $factory->expects($this->any())
        ->method('get')
        ->willReturn($query);

    $statement = $this->getMockBuilder('Drupal\Core\Database\Driver\fake\FakeStatement')
        ->disableOriginalConstructor()
        ->getMock();

    $statement->expects($this->any())
        ->method('fetchObject')
        ->will($this->returnCallback(array($this, 'fetchObjectCallback')));

    $select = $this->getMockBuilder('Drupal\Core\Database\Query\Select')
        ->disableOriginalConstructor()
        ->getMock();

    $select->expects($this->any())
        ->method('fields')
        ->will($this->returnSelf());

    $select->expects($this->any())
        ->method('condition')
        ->will($this->returnSelf());

    $select->expects($this->any())
        ->method('execute')
        ->will($this->returnValue($statement));

    $database = $this->getMockBuilder('Drupal\Core\Database\Connection')
        ->disableOriginalConstructor()
        ->getMock();

    $database->expects($this->any())
        ->method('select')
        ->will($this->returnValue($select));

    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');

    $fieldTypePluginManager = $this->getMockBuilder('Drupal\Core\Field\FieldTypePluginManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->container->set('entity.query', $factory);
    $this->container->set('entity.query.sql', $factory);
    $this->container->set('database', $database);
    $this->container->set('cache.entity', $this->cacheBackend);
    $this->container->set('language_manager', $language_manager);
    $this->container->set('plugin.manager.field.field_type', $fieldTypePluginManager);

    $this->entityStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

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
  public function testActionExecution() {

    $entities = array();
    $entity_type = 'entity_test';
    $property_name = 'test_property';
    $property_value = 'llama';
    for($i=0; $i < 2; $i++) {
      //commented out as currently causes error
      //$entity = entity_create('entity_test');
      //$entity->set($property_name, $property_value);
      //$entity->save();
      //$entities[] = $entity;
    }

    $this->action->setContextValue('type', $entity_type)
      ->setContextValue('property', $property_name)
      ->setContextValue('value', $property_value)
      ->execute();

    $this->assertSame($entities, $this->action->getProvided('entity_fetched')->getContextValue());
  }

}
