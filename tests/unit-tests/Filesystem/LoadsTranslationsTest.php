<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

namespace Hyn\Tenancy\Tests\Filesystem;

use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Foundation\Application;

class LoadsTranslationsTest extends Test
{
    /**
     * @var Directory
     */
    protected $directory;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->directory = $app->make(Directory::class);
        $this->directory->setWebsite($this->website);
    }

    /**
     * @test
     * @covers \Hyn\Tenancy\Website\Directory::makeDirectory
     * @covers \Hyn\Tenancy\Website\Directory::exists
     * @covers \Hyn\Tenancy\Website\Directory::put
     * @covers \Hyn\Tenancy\Listeners\Filesystem\LoadsTranslations
     * @covers \Hyn\Tenancy\Events\Hostnames\Identified
     * @covers \Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener
     */
    public function reads_additional_translations()
    {
        // Directory should now exists, let's write the config folder.
        $this->assertTrue($this->directory->makeDirectory('lang'));

        // Write a testing config.
        $this->assertTrue($this->directory->put('lang' . DIRECTORY_SEPARATOR . 'ch' . DIRECTORY_SEPARATOR . 'test.php', <<<EOM
<?php

return [
    'foo' => 'bar'
];
EOM
));

        $this->assertTrue($this->directory->exists('lang/ch/test.php'));

        $this->activateTenant('local');

        if (! $this->isAppVersion('5.3')) {
            $this->assertEquals('bar', trans('test.foo', [], 'ch'));
        } else {
            $this->assertEquals('bar', trans('test.foo', [], 'messages', 'ch'));
        }
    }
}
