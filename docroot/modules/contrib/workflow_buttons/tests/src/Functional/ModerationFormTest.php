<?php

namespace Drupal\Tests\workflow_buttons\Functional;

use Drupal\Tests\content_moderation\Functional\ModerationStateTestBase;
use Drupal\workflows\Entity\Workflow;

/**
 * Tests the moderation form, specifically on nodes.
 *
 * @group workflow_buttons
 */
class ModerationFormTest extends ModerationStateTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'content_moderation',
    'locale',
    'content_translation',
    'workflow_buttons',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
    $this->createContentTypeFromUi('Moderated content', 'moderated_content', TRUE);
    $this->grantUserPermissionToCreateContentOfType($this->adminUser, 'moderated_content');
  }

  /**
   * Tests the moderation form that shows on the latest version page.
   *
   * The latest version page only shows if there is a pending revision.
   *
   * @see \Drupal\content_moderation\EntityOperations
   * @see \Drupal\Tests\content_moderation\Functional\ModerationStateBlockTest::testCustomBlockModeration
   */
  public function testModerationForm() {
    // Create new moderated content in draft.
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'Some moderated content',
      'body[0][value]' => 'First version of the content.',
    ], t('Save and Create New Draft'));

    $node = $this->drupalGetNodeByTitle('Some moderated content');
    $canonical_path = sprintf('node/%d', $node->id());
    $edit_path = sprintf('node/%d/edit', $node->id());
    $latest_version_path = sprintf('node/%d/latest', $node->id());

    $this->assertTrue($this->adminUser->hasPermission('edit any moderated_content content'));

    // The canonical view should have a moderation form, because it is not the
    // live revision.
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertField('edit-new-state', 'The node view page has a moderation form.');

    // The latest version page should not show, because there is no pending
    // revision.
    $this->drupalGet($latest_version_path);
    $this->assertResponse(403);

    // Update the draft.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Second version of the content.',
    ], t('Save and Create New Draft'));

    // The canonical view should have a moderation form, because it is not the
    // live revision.
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertField('edit-new-state', 'The node view page has a moderation form.');

    // The latest version page should not show, because there is still no
    // pending revision.
    $this->drupalGet($latest_version_path);
    $this->assertResponse(403);

    // Publish the draft.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Third version of the content.',
    ], t('Save and Publish'));

    // The published view should not have a moderation form, because it is the
    // live revision.
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertNoField('edit-new-state', 'The node view page has no moderation form.');

    // The latest version page should not show, because there is still no
    // pending revision.
    $this->drupalGet($latest_version_path);
    $this->assertResponse(403);

    // Make a pending revision.
    $this->drupalPostForm($edit_path, [
      'body[0][value]' => 'Fourth version of the content.',
    ], t('Save and Create New Draft'));

    // The published view should not have a moderation form, because it is the
    // live revision.
    $this->drupalGet($canonical_path);
    $this->assertResponse(200);
    $this->assertNoField('edit-new-state', 'The node view page has no moderation form.');

    // The latest version page should show the moderation form and have "Draft"
    // status, because the pending revision is in "Draft".
    $this->drupalGet($latest_version_path);
    $this->assertResponse(200);
    $this->assertField('edit-new-state', 'The latest-version page has a moderation form.');
    $this->assertText('Draft', 'Correct status found on the latest-version page.');

    // Submit the moderation form to change status to published.
    $this->drupalPostForm($latest_version_path, [
      'new_state' => 'published',
    ], t('Apply'));

    // The latest version page should not show, because there is no
    // pending revision.
    $this->drupalGet($latest_version_path);
    $this->assertResponse(403);
  }

  /**
   * Test moderation non-bundle entity type.
   */
  public function testNonBundleModerationForm() {
    $this->drupalLogin($this->rootUser);
    $workflow = Workflow::load('editorial');
    $workflow->getTypePlugin()->addEntityTypeAndBundle('entity_test_mulrevpub', 'entity_test_mulrevpub');
    $workflow->save();

    drupal_flush_all_caches();

    // Create new moderated content in draft.
    $this->drupalPostForm('entity_test_mulrevpub/add', [], t('Save and Create New Draft'));

    // The latest version page should not show, because there is no pending
    // revision.
    $this->drupalGet('/entity_test_mulrevpub/manage/1/latest');
    $this->assertResponse(403);

    // Update the draft.
    $this->drupalPostForm('entity_test_mulrevpub/manage/1/edit', [], t('Save and Create New Draft'));

    // The latest version page should not show, because there is still no
    // pending revision.
    $this->drupalGet('/entity_test_mulrevpub/manage/1/latest');
    $this->assertResponse(403);

    // Publish the draft.
    $this->drupalPostForm('entity_test_mulrevpub/manage/1/edit', [], t('Save and Publish'));

    // The published view should not have a moderation form, because it is the
    // default revision.
    $this->drupalGet('entity_test_mulrevpub/manage/1');
    $this->assertResponse(200);
    $this->assertNoText('Status', 'The node view page has no moderation form.');

    // The latest version page should not show, because there is still no
    // pending revision.
    $this->drupalGet('entity_test_mulrevpub/manage/1/latest');
    $this->assertResponse(403);

    // Make a pending revision.
    $this->drupalPostForm('entity_test_mulrevpub/manage/1/edit', [], t('Save and Create New Draft'));

    // The published view should not have a moderation form, because it is the
    // default revision.
    $this->drupalGet('entity_test_mulrevpub/manage/1');
    $this->assertResponse(200);
    $this->assertNoText('Status', 'The node view page has no moderation form.');

    // The latest version page should show the moderation form and have "Draft"
    // status, because the pending revision is in "Draft".
    $this->drupalGet('entity_test_mulrevpub/manage/1/latest');
    $this->assertResponse(200);
    $this->assertText('Draft', 'Correct status found on the latest-version page.');

    // Submit the moderation form to change status to published.
    $this->drupalPostForm('entity_test_mulrevpub/manage/1/latest', [
      'new_state' => 'published',
    ], t('Apply'));

    // The latest version page should not show, because there is no
    // pending revision.
    $this->drupalGet('entity_test_mulrevpub/manage/1/latest');
    $this->assertResponse(403);
  }

  /**
   * Tests translated and moderated nodes.
   */
  public function testContentTranslationNodeForm() {
    $this->drupalLogin($this->rootUser);

    // Add French language.
    $edit = [
      'predefined_langcode' => 'fr',
    ];
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    // Enable content translation on articles.
    $this->drupalGet('admin/config/regional/content-language');
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][moderated_content][translatable]' => TRUE,
      'settings[node][moderated_content][settings][language][language_alterable]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Adding languages requires a container rebuild in the test running
    // environment so that multilingual services are used.
    $this->rebuildContainer();

    // Create new moderated content in draft (revision 1).
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'Some moderated content',
      'body[0][value]' => 'First version of the content.',
    ], t('Save and Create New Draft'));
    $this->assertTrue($this->xpath('//ul[@class="entity-moderation-form"]'));

    $node = $this->drupalGetNodeByTitle('Some moderated content');
    $this->assertTrue($node->language(), 'en');
    $edit_path = sprintf('node/%d/edit', $node->id());
    $translate_path = sprintf('node/%d/translations/add/en/fr', $node->id());
    $latest_version_path = sprintf('node/%d/latest', $node->id());
    $french = \Drupal::languageManager()->getLanguage('fr');

    $this->drupalGet($latest_version_path);
    $this->assertSession()->statusCodeEquals('403');
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Add french translation (revision 2).
    $this->drupalGet($translate_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Second version of the content.',
    ], t('Save and Publish (this translation)'));

    $this->drupalGet($latest_version_path, ['language' => $french]);
    $this->assertSession()->statusCodeEquals('403');
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Add french pending revision (revision 3).
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Third version of the content.',
    ], t('Save and Create New Draft (this translation)'));

    $this->drupalGet($latest_version_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//ul[@class="entity-moderation-form"]'));

    // It should not be possible to add a new english revision.
    $this->drupalGet($edit_path);
    $this->assertFalse($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->assertSession()->pageTextContains('Unable to save this Moderated content.');

    $this->clickLink('Publish');
    $this->assertSession()->fieldValueEquals('body[0][value]', 'Third version of the content.');

    $this->drupalGet($edit_path);
    $this->clickLink('Delete');
    $this->assertSession()->buttonExists('Delete');

    $this->drupalGet($latest_version_path);
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Publish the french pending revision (revision 4).
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Fifth version of the content.',
    ], t('Save and Publish (this translation)'));

    $this->drupalGet($latest_version_path, ['language' => $french]);
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Now we can publish the english (revision 5).
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Sixth version of the content.',
    ], t('Save and Publish (this translation)'));

    $this->drupalGet($latest_version_path);
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Make sure we're allowed to create a pending french revision.
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));

    // Add a english pending revision (revision 6).
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Seventh version of the content.',
    ], t('Save and Create New Draft (this translation)'));

    $this->drupalGet($latest_version_path);
    $this->assertTrue($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Make sure we're not allowed to create a pending french revision.
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertFalse($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->assertSession()->pageTextContains('Unable to save this Moderated content.');

    $this->drupalGet($latest_version_path, ['language' => $french]);
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // We should be able to publish the english pending revision (revision 7)
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Eighth version of the content.',
    ], t('Save and Publish (this translation)'));

    $this->drupalGet($latest_version_path);
    $this->assertFalse($this->xpath('//ul[@class="entity-moderation-form"]'));

    // Make sure we're allowed to create a pending french revision.
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));

    // Make sure we're allowed to create a pending english revision.
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));

    // Create new moderated content. (revision 1).
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'Second moderated content',
      'body[0][value]' => 'First version of the content.',
    ], t('Save and Publish'));

    $node = $this->drupalGetNodeByTitle('Second moderated content');
    $this->assertTrue($node->language(), 'en');
    $edit_path = sprintf('node/%d/edit', $node->id());
    $translate_path = sprintf('node/%d/translations/add/en/fr', $node->id());

    // Add a pending revision (revision 2).
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [
      'body[0][value]' => 'Second version of the content.',
    ], t('Save and Create New Draft (this translation)'));

    // It shouldn't be possible to translate as we have a pending revision.
    $this->drupalGet($translate_path);
    $this->assertFalse($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->assertSession()->pageTextContains('Unable to save this Moderated content.');

    // Create new moderated content (revision 1).
    $this->drupalPostForm('node/add/moderated_content', [
      'title[0][value]' => 'Third moderated content',
    ], t('Save and Publish'));

    $node = $this->drupalGetNodeByTitle('Third moderated content');
    $this->assertTrue($node->language(), 'en');
    $edit_path = sprintf('node/%d/edit', $node->id());
    $translate_path = sprintf('node/%d/translations/add/en/fr', $node->id());

    // Translate it, without updating data (revision 2).
    $this->drupalGet($translate_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [], t('Save and Create New Draft (this translation)'));

    // Add another draft for the translation (revision 3).
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [], t('Save and Create New Draft (this translation)'));

    // Editing the original translation should not be possible.
    $this->drupalGet($edit_path);
    $this->assertFalse($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->assertSession()->pageTextContains('Unable to save this Moderated content.');

    // Updating and publishing the french translation is still possible.
    $this->drupalGet($edit_path, ['language' => $french]);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertFalse($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [], t('Save and Publish (this translation)'));

    // Now the french translation is published, an english draft can be added.
    $this->drupalGet($edit_path);
    $this->assertTrue($this->xpath('//input[@value="Save and Create New Draft (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Publish (this translation)"]'));
    $this->assertTrue($this->xpath('//input[@value="Save and Archive (this translation)"]'));
    $this->drupalPostForm(NULL, [], t('Save and Create New Draft (this translation)'));
  }

}
