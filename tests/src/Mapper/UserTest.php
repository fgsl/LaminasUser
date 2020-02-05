<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUserTest\Mapper;

use LaminasUser\Entity\User as Entity;
use LaminasUser\Mapper\User as Mapper;
use LaminasUser\Mapper\UserHydrator;
use Laminas\Db\Adapter\Adapter;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    /** @var \LaminasUser\Mapper\User */
    protected $mapper;

    /** @var \Laminas\Db\Adapter\Adapter */
    protected $mockedDbAdapter;

    /** @var \Laminas\Db\Adapter\Adapter */
    protected $realAdapter = array();

    /** @var \Laminas\Db\Sql\Select */
    protected $mockedSelect;

    /** @var \Laminas\Db\ResultSet\HydratingResultSet */
    protected $mockedResultSet;

    /** @var \Laminas\Db\Sql\Sql */
    protected $mockedDbSql;

    /** @var \Laminas\Db\Adapter\Driver\DriverInterface */
    protected $mockedDbAdapterDriver;

    /** @var \Laminas\Db\Adapter\Platform\PlatformInterface */
    protected $mockedDbAdapterPlatform;

    public function setUp(): void
    {
        $this->mapper = $this->createMock(Mapper::class);
        $this->mapper->setEntityPrototype(new Entity());
        $this->mapper->setHydrator(new UserHydrator());
        $this->mapper->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('user'));

        $this->setUpMockedAdapter();

        $this->mockedSelect = $this->createMock('\Laminas\Db\Sql\Select', array(
            'where'
        ));
        $this->mockedResultSet = $this->createMock('\Laminas\Db\ResultSet\HydratingResultSet');

        $this->setUpAdapter('mysql');
        // $this->setUpAdapter('pgsql');
        $this->setUpAdapter('sqlite');
    }

    /**
     */
    public function setUpAdapter($driver)
    {
        $upCase = strtoupper($driver);
        if (! defined(sprintf('DB_%s_DSN', $upCase)) || ! defined(sprintf('DB_%s_USERNAME', $upCase)) || ! defined(sprintf('DB_%s_PASSWORD', $upCase)) || ! defined(sprintf('DB_%s_SCHEMA', $upCase))) {
            return false;
        }

        try {
            $connection = array(
                'driver' => sprintf('Pdo_%s', ucfirst($driver)),
                'dsn' => constant(sprintf('DB_%s_DSN', $upCase))
            );
            if (constant(sprintf('DB_%s_USERNAME', $upCase)) !== "") {
                $connection['username'] = constant(sprintf('DB_%s_USERNAME', $upCase));
                $connection['password'] = constant(sprintf('DB_%s_PASSWORD', $upCase));
            }
            $adapter = new Adapter($connection);

            $this->setUpSqlDatabase($adapter, constant(sprintf('DB_%s_SCHEMA', $upCase)));

            $this->realAdapter[$driver] = $adapter;
        } catch (\Exception $e) {
            $this->realAdapter[$driver] = false;
        }
    }

    public function setUpSqlDatabase($adapter, $schemaPath)
    {
        $queryStack = array(
            'DROP TABLE IF EXISTS user'
        );
        $queryStack = array_merge($queryStack, explode(';', file_get_contents($schemaPath)));
        $queryStack = array_merge($queryStack, explode(';', file_get_contents(__DIR__ . '/_files/user.sql')));

        foreach ($queryStack as $query) {
            if (! preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     */
    public function setUpMockedAdapter()
    {
        $this->mockedDbAdapterDriver = $this->createMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $this->mockedDbAdapterPlatform = $this->createMock('Laminas\Db\Adapter\Platform\PlatformInterface', array());
        $this->mockedDbAdapterStatement = $this->createMock('Laminas\Db\Adapter\Driver\StatementInterface', array());

        $this->mockedDbAdapterPlatform->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('null'));

        $this->mockedDbAdapter = $this->getMockBuilder('Laminas\Db\Adapter\Adapter')
            ->setConstructorArgs(array(
            $this->mockedDbAdapterDriver,
            $this->mockedDbAdapterPlatform
        ))
            ->getMock(array(
            'getPlatform'
        ));

        $this->mockedDbAdapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($this->mockedDbAdapterPlatform));

        $this->mockedDbSql = $this->getMockBuilder('Laminas\Db\Sql\Sql')
            ->setConstructorArgs(array(
            $this->mockedDbAdapter
        ))
            ->onlyMethods(array(
            'prepareStatementForSqlObject'
        ))
            ->getMock();
        $this->mockedDbSql->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->will($this->returnValue($this->mockedDbAdapterStatement));

        $this->mockedDbSqlPlatform = $this->getMockBuilder('\Laminas\Db\Sql\Platform\Platform')
            ->setConstructorArgs(array(
            $this->mockedDbAdapter
        ))
            ->getMock();
    }

    public function testGetTableName()
    {
        $this->assertEquals('user', $this->mapper->getTableName());
    }

    public function providerTestFindBy()
    {
        $user = new Entity();
        $user->setEmail('laminas-user@fgsl.eti.br');
        $user->setUsername('laminas-user');
        $user->setDisplayName('Laminas-User');
        $user->setId('1');
        $user->setState(1);
        $user->setPassword('laminas-user');

        return array(
            array(
                'findByEmail',
                array(
                    $user->getEmail()
                ),
                array(
                    'whereArgs' => array(
                        array(
                            'email' => $user->getEmail()
                        ),
                        'AND'
                    )
                ),
                array(),
                $user
            ),
            array(
                'findByUsername',
                array(
                    $user->getUsername()
                ),
                array(
                    'whereArgs' => array(
                        array(
                            'username' => $user->getUsername()
                        ),
                        'AND'
                    )
                ),
                array(),
                $user
            ),
            array(
                'findById',
                array(
                    $user->getId()
                ),
                array(
                    'whereArgs' => array(
                        array(
                            'user_id' => $user->getId()
                        ),
                        'AND'
                    )
                ),
                array(),
                $user
            )
        );
    }
}
