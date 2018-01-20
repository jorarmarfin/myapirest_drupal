<?php

namespace Drupal\my_api_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "my_api_rest_resource",
 *   label = @Translation("My Api rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/gestion",
 *     "https://www.drupal.org/link-relations/create" = "/api/gestion"
 *   }
 * )
 */
class MyApiRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new FeedbackRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alerta_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
   public function post($data) {
    $response = 0;
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    switch ($data['action']['value']) {
      case 'U':
        $node = Node::load($data['nid']['value']);
        if($node->title->value)$node->set('title',$data['title']['value']);
        if($node->field_monto->value)$node->set('field_monto',$data['monto']['value']);
        if($node->field_fecha->value)$node->set('field_fecha',$data['fecha']['value']);
        if($node->body->value)$node->set('body',$data['body']['value']);
        if($node->save()){
            $response = 1;
          }
        break;
      case 'D':
        $node = Node::load($data['nid']['value']);
        if(!$node->delete()){
            $response = 1;
          }
        break;

      default:
          $node = Node::create(
            array(
              'type' => $data['tipo']['value'],
              'title' => $data['title']['value'],
              'field_monto' => $data['monto']['value'],
              'field_fecha' => $data['fecha']['value'],
              'body' => [
                'value' => $data['body']['value']
              ],
            )
          );
          if($node->save()){
            $response = 1;
          }
        break;
    }

    return new ResourceResponse($response);
  }

}
