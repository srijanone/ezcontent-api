<?php

namespace Drupal\ezcontent_preview;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\NodeInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\access_unpublished\AccessTokenManager;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Utility class.
 */
class Utils {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The time service.
   *
   * @var \Drupal\access_unpublished\AccessTokenManager
   */
  protected $accessToken;

  /**
   * The default language.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected $defaultLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, MessengerInterface $messenger, AliasManagerInterface $aliasManager, TimeInterface $time, AccessTokenManager $accessToken, LanguageDefault $defaultLanguage) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
    $this->aliasManager = $aliasManager;
    $this->time = $time;
    $this->accessToken = $accessToken;
    $this->defaultLanguage = $defaultLanguage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('path_alias.manager'),
      $container->get('datetime.time'),
      $container->get('access_unpublished.access_token_manager'),
      $container->get('language.default')
    );
  }

  /**
   * Builds preview url on the basis of config and node path alias.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node interface.
   * @param string $decoupledRoutes
   *   The routing config.
   * @param array $options
   *   The config options.
   *
   * @return string $siteUrl
   *   The URL path.
   */
  public function buildUrl(NodeInterface $node, $decoupledRoutes, $options = []) {
    $preview_base_url = $decoupledRoutes->url;
    if (!$preview_base_url) {
      $this->messenger->addMessage('Add frontend URL in module config form to view decoupled preview.', 'custom');
      return FALSE;
    }
    $node_id = $node->id();
    // Fetch node's language based url alias.
    $node_language = $node->language()->getId();
    $node_alias = $this->aliasManager->getAliasByPath('/node/' . $node_id, $node_language);

    // If node is unpublished using
    // https://www.drupal.org/project/access_unpublished
    // module, then it should generate token and pass it to Drupal.
    // OR if node is published, but its latest revision is not published, then
    // it should fetch the "rel:working-copy" of the node and generate a token
    // to access the same.
    if (!$node->isPublished() || ($node->isPublished() && $node->getEntityType()
          ->isRevisionable() && !$node->isLatestRevision())) {
      $tokenKey = $this->configFactory->get('access_unpublished.settings')
        ->get('hash_key');
      $activeToken = $this->accessToken->getActiveAccessToken($node);
      if (!$activeToken) {
        $activeToken = $this->buildToken($node);
      }
      $tokenValue = $activeToken->get('value')->value;
      $options['query'] = [
        $tokenKey => $tokenValue,
      ];
    }
    // Parameter that denotes, working-copy is to be fetched for the node.
    if ($node->isPublished() && $node->getEntityType()->isRevisionable()
      && !$node->isLatestRevision()) {
      $options['query']['resourceVersion'] = 'rel:working-copy';
    }
    // Pass language code in url, only if we get node's language as other than
    // 'en'.
    $lang_code = $node_language != $this->defaultLanguage->get() ? '/' . $node_language : '';
    $siteUrl = Url::fromUri($preview_base_url . $lang_code . $node_alias, $options);
    return $siteUrl;
  }

  /**
   * Token bulding.
   *
   * @param object $entity
   *   Entity object.
   *
   * @return \Drupal\Core\Entity\EntityInterface $access_token
   *   The Access Unpublished Token.
   */
  public function buildToken($entity) {
    $tokenKey = $this->configFactory->get('ezcontent_preview.settings')
      ->get('ezcontent_preview_token_expire_time');
    if (!$tokenKey) {
      $tokenKey = 300;
    }
    $access_token = $this->entityTypeManager->getStorage('access_token')
      ->create(
        [
          'entity_type' => $entity->getEntityType()->id(),
          'entity_id' => $entity->id(),
          'expire' => $this->time->getRequestTime() + $tokenKey,
        ]
      );
    $access_token->save();
    return $access_token;
  }

}
