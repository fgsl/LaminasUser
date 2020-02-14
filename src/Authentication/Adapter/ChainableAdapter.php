<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUser\Authentication\Adapter;

use Laminas\Authentication\Storage\StorageInterface;


interface ChainableAdapter
{
    /**
     * @param AdapterChainEvent $e
     * @return bool
     */
    public function authenticateEvent(AdapterChainEvent $e);

    /**
     * @return StorageInterface
     */
    public function getStorage();
}
