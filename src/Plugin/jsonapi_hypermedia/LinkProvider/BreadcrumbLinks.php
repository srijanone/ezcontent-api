<?php

namespace Drupal\ezcontent_api\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ezcontent_api\Breadcrumb\EzcontentBreadcrumbBuilder;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Link plugin for article resource.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_hypermedia.breadcrumb",
 *   link_relation_type = "node",
 *   link_context = {
 *    "resource_object" = "node--article",
 *   }
 * )
 */
class BreadcrumbLinks extends LinkProviderBase implements ContainerFactoryPluginInterface {

  /**
   * The plugin_id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurableInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The breadcrumb builder object.
   *
   * @var \Drupal\ezcontent_api\Breadcrumb\EzcontentBreadcrumbBuilder
   */
  protected $ezconteBreadcrumbBuilder;

  /**
   * The RouteMatchInterface object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   *   The current route match.
   */
  protected $routeMatch;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ezcontent_api\Breadcrumb\EzcontentBreadcrumbBuilder $ezconteBreadcrumbBuilder
   *   The breadcrumb builder object.*.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The RouteMatchInterface object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EzcontentBreadcrumbBuilder $ezconteBreadcrumbBuilder, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ezconteBreadcrumbBuilder = $ezconteBreadcrumbBuilder;
    $this->routeMatch = $routeMatch;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ezcontent_api_normalizer.ezcontent_breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    $entity = $this->routeMatch->getParameter('entity');
    $linkAttributes = [];
    $url = Url::fromRoute('<front>');
    $access_result = AccessResult::allowedIf(FALSE);
    if ($entity instanceof NodeInterface) {
      $breadCrumb = $this->ezconteBreadcrumbBuilder->build($this->routeMatch);
      $breadcrumbLinks = [];
      foreach ($breadCrumb->getLinks() as $link) {
        $breadcrumbLinks[] = [
          'text' => ($link instanceof Link) ? $link->getText() : $link,
          'url' => ($link instanceof Link) ? $link->getUrl()->toString() : '',
        ];
      }
      $linkAttributes = [
        'data' => $breadcrumbLinks,
      ];
      $url = Url::fromRoute('entity.node.canonical', ['node' => $entity->id()]);
      $access_result = AccessResult::allowedIf($entity->access('view'));
    }
    return AccessRestrictedLink::createLink($access_result, CacheableMetadata::createFromObject($resource_object), $url, $this->getLinkRelationType(), $linkAttributes);
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkRelationType() {
    return 'breadcrumb';
  }

}
