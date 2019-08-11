<?php
declare(strict_types = 1);

namespace LanguageServer\Client;

use LanguageServer\ClientHandler;
use LanguageServerProtocol\TextDocumentIdentifier;
use Sabre\Event\Promise;
use JsonMapper;
use stdClass;

/**
 * Provides method handlers for all workspace/* methods
 */
class Workspace
{
    /**
     * @var ClientHandler
     */
    private $handler;

    /**
     * @var JsonMapper
     */
    private $mapper;

    public function __construct(ClientHandler $handler, JsonMapper $mapper)
    {
        $this->handler = $handler;
        $this->mapper = $mapper;
    }

    /**
     * Returns a list of all files in a directory
     *
     * @param string $base The base directory (defaults to the workspace)
     * @return Promise <TextDocumentIdentifier[]> Array of documents
     */
    public function xfiles(string $base = null): Promise
    {
        return $this->handler->request(
            'workspace/xfiles',
            ['base' => $base]
        )->then(function (array $textDocuments) {
            return $this->mapper->mapArray($textDocuments, [], TextDocumentIdentifier::class);
        });
    }

    /**
     * Request one configuration item
     * 
     * @param string $section The configuration section asked for
     * @param string $scopeUri The scope to get the configuration section for
     * @return Promise <mixed> Requested configuration section
     */
    public function configurationItem(string $section = null, string $scopeUri = null): Promise
    {
        $item = new stdClass();
        if ($scopeUri) {
            $item->scopeUri = $scopeUri;
        }
        if ($section) {
            $item->section = $section;
        }
        return $this->configuration([$item])->then(function (array $configuration) {
            return $configuration[0];
        });
    }

    /**
     * Request multiple configuration items
     * 
     * @param array[]|object[] $items
     *     Single item in items array must follow the ConfigurationItem interface
     *     (https://microsoft.github.io/language-server-protocol/specification#workspace_configuration)
     *     - scopeUri?: string
     *     - section?: string
     * 
     * @return Promise <mixed[]> Requested configuration section in the requested order
     */
    public function configuration(array $items): Promise
    {
        return $this->handler->request(
            'workspace/configuration',
            ['items' => $items]
        );
    }
}
