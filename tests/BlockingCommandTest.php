<?php
use mikehaertl\shellcommand\Command;

class BlockingCommandTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        // Default in some installations
        setlocale(LC_CTYPE, 'C');
    }

    // Create command from command string
    public function testCanPassCommandStringToConstructor()
    {
        $command = new Command('/bin/ls -l');

        $this->assertEquals('/bin/ls -l', $command->getExecCommand());
        $this->assertNull($command->nonBlockingMode);
    }

    // Options
    public function testCanSetOptions()
    {
        $command = new Command;
        $command->setOptions(array(
            'command' => 'echo',
            'escapeArgs' => false,
            'procEnv' => array('TESTVAR' => 'test'),
            'args' => '-n $TESTVAR',
            'nonBlockingMode' => false,
        ));
        $this->assertFalse($command->nonBlockingMode);
        $this->assertEquals('echo -n $TESTVAR', $command->getExecCommand());
        $this->assertFalse($command->escapeArgs);
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals('test', $command->getOutput());
    }
    public function testCanPassOptionsToConstructor()
    {
        $command = new Command(array(
            'command' => 'echo',
            'args' => '-n $TESTVAR',
            'escapeArgs' => false,
            'procEnv' => array('TESTVAR' => 'test'),
            'nonBlockingMode' => false,
        ));
        $this->assertFalse($command->nonBlockingMode);
        $this->assertEquals('echo -n $TESTVAR', $command->getExecCommand());
        $this->assertFalse($command->escapeArgs);
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals('test', $command->getOutput());
    }


    public function testCanRunCommandWithArguments()
    {
        $command = new Command('ls');
        $command->nonBlockingMode = false;
        $command->addArg('-l');
        $command->addArg('-n');
        $this->assertEquals("ls '-l' '-n'", $command->getExecCommand());
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
    }

    // Output / error / exit code
    public function testCanRunValidCommand()
    {
        $dir = __DIR__;
        $command = new Command("/bin/ls $dir/Command*");
        $command->nonBlockingMode = false;

        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals("$dir/CommandTest.php", $command->getOutput());
        $this->assertEquals("$dir/CommandTest.php\n", $command->getOutput(false));
        $this->assertEmpty($command->getError());
        $this->assertEmpty($command->getStdErr());
        $this->assertEquals(0, $command->getExitCode());
    }
    public function testCanNotRunEmptyCommand()
    {
        $command = new Command('');
        $command->nonBlockingMode = false;
        $this->assertFalse($command->execute());
        $this->assertEquals('Could not locate any executable command', $command->getError());
    }
    public function testCanNotRunNotExistantCommand()
    {
        $command = new Command('/does/not/exist');
        $command->nonBlockingMode = false;
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertEmpty($command->getOutput());
        $this->assertEquals(127, $command->getExitCode());
    }
    public function testCanNotRunInvalidCommand()
    {
        $command = new Command('ls --this-does-not-exist');
        $command->nonBlockingMode = false;
        $this->assertFalse($command->getExecuted());
        $this->assertFalse($command->execute());
        $this->assertFalse($command->getExecuted());
        $this->assertNotEmpty($command->getError());
        $this->assertNotEmpty($command->getStdErr());
        $this->assertEmpty($command->getOutput());
        $this->assertEquals(2, $command->getExitCode());
    }


    // Proc
    public function testCanProvideProcEnvVars()
    {
        $command = new Command('echo $TESTVAR');
        $command->nonBlockingMode = false;
        $command->procEnv = array('TESTVAR' => 'testvalue');
        $this->assertTrue($command->execute());
        $this->assertEquals("testvalue", $command->getOutput());
    }
    public function testCanProvideProcDir()
    {
        $tmpDir = sys_get_temp_dir();
        $command = new Command('pwd');
        $command->procCwd = $tmpDir;
        $command->nonBlockingMode = false;
        $this->assertFalse($command->getExecuted());
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals($tmpDir, $command->getOutput());
    }
    public function testCanRunCommandWithStandardInput()
    {
        $command = new Command('/bin/cat');
        $command->nonBlockingMode = false;
        $command->addArg('-T');
        $command->setStdIn("\t");
        $this->assertTrue($command->execute());
        $this->assertTrue($command->getExecuted());
        $this->assertEquals("^I", $command->getOutput());
    }
}
