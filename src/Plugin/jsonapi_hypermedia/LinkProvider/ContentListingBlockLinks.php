<?php

namespace Drupal\ezcontent_api\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ezcontent_api\Breadcrumb\EzcontentBreadcrumbBuilder;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\ezcontent_listing\ContentListingHelperBlock;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Link plugin for article resource.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_hypermedia.content_listing",
 *   link_context = {
 *    "resource_object" = "block_content--content_listing_component",
 *   }
 * )
 */
class ContentListingBlockLinks extends LinkProviderBase implements ContainerFactoryPluginInterface {

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
   * The RouteMatchInterface object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   *   The current route match.
   */
  protected $routeMatch;

  /**
   * The Content list helper block object.
   *
   * @var \Drupal\ezcontent_listing\ContentListingHelperBlock
   *   The content list helper block.
   */
  protected $contentListingHelperBlock;

  /**
   * The RequestStack object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   *   The requestStack object.
   */
  protected $requestStack;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The RouteMatchInterface object.
   * @param \Drupal\ezcontent_listing\ContentListingHelperBlock $contentListingHelperBlock
   *   The RouteMatchInterface object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch, ContentListingHelperBlock $contentListingHelperBlock, RequestStack $requestStack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->contentListingHelperBlock = $contentListingHelperBlock;
    $this->requestStack = $requestStack;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('current_route_match'),
      $container->get('ezcontent_listing.helper_block'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    $entity = $this->routeMatch->getParameter('entity');
    $page = is_numeric($this->requestStack->getCurrentRequest()->query->get('page')) ?
      intval($this->requestStack->getCurrentRequest()->query->get('page')) : 0;
    $linkAttributes = [];
    $access_result = AccessResult::allowedIf(FALSE);
    $url = Url::fromRoute('<front>');
    $cacheMetaData = CacheableMetadata::createFromObject($resource_object);
    if ($entity instanceof BlockContent) {
      $blockViewsData = $this->contentListingHelperBlock->getContentListingBlock($entity, 'result', $page);
      // Add cache context based on page argument.
      $cacheMetaData->addCacheContexts(['url.query_args:page']);
      $nodes = [];
      foreach ($blockViewsData['rows'] as $data) {
        $nodes[] = $data->_entity;
      }
      $linkAttributes['data'] = $nodes;
      $linkAttributes['total_rows'] = (string) $blockViewsData['total_rows'];
      $linkAttributes['item_per_page'] = (string) $blockViewsData['item_per_page'];
      $access_result = AccessResult::allowedIf($entity->access('view'));
    }
    return AccessRestrictedLink::createLink($access_result, $cacheMetaData, $url, $this->getLinkRelationType(), $linkAttributes);
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkRelationType() {
    return 'content_listing';
  }

}
