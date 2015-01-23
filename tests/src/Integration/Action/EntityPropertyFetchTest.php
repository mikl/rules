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
    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
  }

}
