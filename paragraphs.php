<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Implements hook_form_alter().
 */
function CUSTOM_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  if ($form_id == 'search_block_form') {
    $form['keys']['#attributes']['placeholder'] = t('Enter your search term here...');
    unset($form['actions']['submit']);
  }
}

/**
 * Nodes
 */

function CUSTOM_preprocess_node__job_post(&$variables) {
  $node = $variables['node'];
  $address = $node->field_address;
  $variables['address_short'] = sprintf("%s, %s", $address->locality, $address->administrative_area);
}

/**
 * Paragraphs.
 */

// Full row HTML paragraph
function CUSTOM_preprocess_paragraph__full_row_html(&$variables) {
  $paragraph = $variables['paragraph'];
  if (!$paragraph->field_background_image->isEmpty()) {
    $image = file_create_url($paragraph->field_background_image->entity->field_media_image->entity->getFileUri());
    $variables['attributes']['style'][] = 'background-image: url("' . $image . '");';
    $variables['attributes']['style'][] = 'background-size: cover;';
    $variables['attributes']['style'][] = 'background-position: center center;';
  }
  $color = $paragraph->field_background_color->value;
  $variables['attributes']['style'][] = 'background-color: ' . $color . ';';
}

// Best practices paragraph
function CUSTOM_preprocess_paragraph__best_practices_section(&$variables) {
  $paragraph = $variables['paragraph'];
  if (!$paragraph->field_background_image->isEmpty()) {
    $image = file_create_url($paragraph->field_background_image->entity->field_media_image->entity->getFileUri());
    $variables['attributes']['style'][] = 'background-image: url("' . $image . '");';
    $variables['attributes']['style'][] = 'background-size: cover;';
    $variables['attributes']['style'][] = 'background-position: center center;';
  }
}

// Full ad row
function CUSTOM_preprocess_paragraph__full_row_ad_space(&$variables) {
  $paragraph = $variables['paragraph'];
  $variables['ad_code'] = $paragraph->field_script->value;
}

// Four column member
function CUSTOM_preprocess_paragraph__four_columns_member_center(&$variables) {
  $paragraph = $variables['paragraph'];
  if (!$paragraph->field_icon->isEmpty()) {
    $variables['icon'] = $paragraph->field_icon->entity->getName();
  }
}

// Four column news
function CUSTOM_preprocess_paragraph__four_columns_news(&$variables) {
  $paragraph = $variables['paragraph'];
  if (!$paragraph->field_news_module_type->isEmpty()) {
    $variables['automatic'] = $paragraph->field_news_module_type->value;
  }
  $term = $paragraph->getParentEntity();
  $variables['tid'] = $term->id();
}

// View all in topic paragraph
function CUSTOM_preprocess_paragraph__view_all_on_topic_section(&$variables) {
  global $base_url;

  $paragraph = $variables['paragraph'];
  $term = $paragraph->getParentEntity();
  $options = [
    'attributes' => [
      'class' => [
        'btn',
        'btn-primary',
      ],
    ],
  ];
  $url = Url::fromUri($base_url . '/' . 'topic-search/' . urlencode($term->getName()), $options);
  $link = Link::fromTextAndUrl('View all on ' . $term->getName(), $url);
  $variables['link'] = $link->toRenderable();
  if (!$paragraph->field_background_image->isEmpty()) {
    $image = file_create_url($paragraph->field_background_image->entity->field_media_image->entity->getFileUri());
    $variables['attributes']['style'][] = 'background-image: url("' . $image . '");';
    $variables['attributes']['style'][] = 'background-size: cover;';
    $variables['attributes']['style'][] = 'background-position: center center;';
  }
}

// Full row HTML paragraph
// Auxiliary themes declared on icma_cusom_blocks.module
function CUSTOM_preprocess_paragraph__page_intro_and_top_stories(&$variables) {
  $paragraph = $variables['paragraph'];
  if (!$paragraph->field_background_image->isEmpty()) {
    $style = ImageStyle::load('front_about');
    $image_uri = $paragraph->field_background_image->entity->field_media_image->entity->getFileUri();
    $about_image = $style->buildUrl($image_uri);
  }
  else {
    $about_image = '#';
  }
  $about_text = $paragraph->field_description->value;
  $buttons = $paragraph->field_buttons;
  $rendered_buttons = [];
  foreach($buttons as $button) {
    if (!$button->entity->field_image->isEmpty()) {
      $image = file_create_url($button->entity->field_image->entity->field_media_image->entity->getFileUri());
      $link_url = Url::fromUri($button->entity->field_link->uri);
      $text = $button->entity->field_link->title;
      $rendered_buttons[] = sprintf("<div class='col image-link'><a href='%s'><img src='%s'/><div class='link-text'>%s</div></a></div>", $link_url->toString(), $image, $text);
    }
  }
  $variables['about'] = [
    '#theme' => 'icma_image_card',
    '#image' => $about_image,
    '#card_body' => sprintf("<div class='top_intro_container'>%s</div><div class='row'>%s</div>", $about_text, implode('', $rendered_buttons)),
  ];
  $variables['ad_code'] = $paragraph->field_ad_code_area->value . $paragraph->field_ad_code->value;
  $top_stories = [];
  $stories = $paragraph->field_top_stories;
  $style = ImageStyle::load('front_slider');
  foreach($stories as $story) {
    if (!$story->entity->field_lead_image->isEmpty()) {
      $image_uri = $story->entity->field_lead_image->entity->field_media_image->entity->getFileUri();
      $top_image = $style->buildUrl($image_uri);
    }
    else {
      $top_image = '#';
    }
    $type = _get_node_type_reference($story);
    $html = sprintf("<div class='type text-white'>%s</div><h2 class='text-white'>%s</h2><div class='description text-white'>%s</div>", $type, $story->entity->title->value, $story->entity->field_description->value);
    $top_stories[] = [
      'image'=> $top_image,
      'carousel_body' => _linked_node($story->entity->id(), $html),
    ];
  }
  $variables['top_stories'] = [
    '#theme' => 'icma_bootstrap_carousel',
    '#items' => $top_stories,
  ];
  $secondary = $paragraph->field_secondary_stories;
  $style = ImageStyle::load('secondary_image_front');
  if (!$secondary[0]->entity->field_lead_image->isEmpty()) {
    $image_uri = $secondary[0]->entity->field_lead_image->entity->field_media_image->entity->getFileUri();
    $secondary_image = $style->buildUrl($image_uri);
  }
  else {
    $secondary_image = '#';
  }
  $type = _get_node_type_reference($secondary[0]);
  $html = sprintf("<div><div class='type text-white'>%s</div><h4 class='title text-white'>%s</h4></div>", $type, $secondary[0]->entity->title->value);
  $variables['secondary_story_1'] = [
    '#theme' => 'icma_image_card',
    '#image' => $secondary_image,
    '#card_body' =>$html,
    '#link' => $url = Url::fromUri('internal:/node/' . $secondary[0]->entity->id()),
  ];
  if (!$secondary[1]->entity->field_lead_image->isEmpty()) {
    $image_uri = $secondary[1]->entity->field_lead_image->entity->field_media_image->entity->getFileUri();
    $secondary_image = $style->buildUrl($image_uri);
  }
  else {
    $secondary_image = '#';
  }
  $type = _get_node_type_reference($secondary[1]);
  $html = sprintf("<div><div class='type text-white'>%s</div><h4 class='title text-white'>%s</h4></div>", $type, $secondary[1]->entity->title->value);
  $variables['secondary_story_2'] = [
    '#theme' => 'icma_image_card',
    '#image' => $secondary_image,
    '#card_body' => $html,
    '#link' => $url = Url::fromUri('internal:/node/' . $secondary[1]->entity->id()),
  ];
}

function _get_node_type_reference($node) {
  if (isset($node->entity->field_article_type)) {
    return $node->entity->field_article_type->entity->label();
  }
  if (isset($node->entity->field_event_type)) {
    return $node->entity->field_event_type->entity->label();
  }
  if (isset($node->entity->field_publication_type)) {
    return $node->entity->field_publication_type->entity->label();
  }
  if (isset($node->entity->field_document_type)) {
    return $node->entity->field_document_type->entity->label();
  }
  if (isset($node->entity->field_type)) {
    return $node->entity->field_type->entity->label();
  }
  else {
    return 'PM';
  }
}

function _linked_node($nid, $html) {
  $url = Url::fromUri('internal:/node/' . $nid);
  return sprintf('<a href="%s">%s</a>', $url->toString(), $html);
}
