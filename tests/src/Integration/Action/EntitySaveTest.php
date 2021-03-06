<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\EntitySaveTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\EntitySave
 * @group rules_action
 */
class EntitySaveTest extends RulesEntityIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->action = $this->actionManager->createInstance('rules_entity_save');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Save entity', $this->action->summary());
  }

  /**
   * Tests the action execution when saving immediately.
   *
   * @covers ::execute
   */
  public function testActionExecutionImmediately() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('save');

    $this->action->setContextValue('entity', $entity)
      ->setContextValue('immediate', TRUE);

    $this->action->execute();
    $this->assertEquals($this->action->autoSaveContext(), [], 'Action returns nothing for auto saving since the entity has been saved already.');
  }

  /**
   * Tests the action execution when saving is postponed.
   *
   * @covers ::execute
   */
  public function testActionExecutionPostponed() {
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->never())
      ->method('save');

    $this->action->setContextValue('entity', $entity);
    $this->action->execute();

    $this->assertEquals($this->action->autoSaveContext(), ['entity'], 'Action returns the entity context name for auto saving.');
  }

}
