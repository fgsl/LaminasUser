<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Authentication\Adapter;

use LaminasUserTest\Authentication\Adapter\TestAsset\AbstractAdapterExtension;
use PHPUnit\Framework\TestCase;

class AbstractAdapterTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AbstractAdapterExtension
     */
    protected $adapter;

    public function setUp():void
    {
        $this->adapter = new AbstractAdapterExtension();
    }
    
    public function test()
    {
        $this->assertTrue(in_array('getCredential',get_class_methods(get_class($this->adapter))));
        $this->assertTrue(in_array('getIdentity',get_class_methods(get_class($this->adapter))));
    }

}
