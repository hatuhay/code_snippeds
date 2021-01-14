$nids = \Drupal::entityQuery('node')
  ->condition('status', 1)
  ->condition('type', 'documento')
  ->execute();
$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
foreach ($nodes as $node) {
  $tid = $node->field_seccion->target_id;
  $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid);
  $list = [];
  foreach ($ancestors as $term) {
    $list[] = $term->id();
  }
  $node->field_seccion = $list;
  $node->save();
}
