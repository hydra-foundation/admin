<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\AdminServiceProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ShippedViewsTest extends TestCase
{
    /**
     * The admin renders by name, and a name with no file behind it fails at the
     * moment somebody opens the screen. Every name the package writes down —
     * in a controller or in one of its own templates — has to be one it ships,
     * or the application is quietly expected to supply it.
     */
    public function test_every_template_the_admin_names_is_one_it_ships(): void
    {
        $names = $this->templateNames();

        $this->assertContains('admin/screen', $names, 'the template scan found nothing it should have');

        foreach ($names as $name) {
            $this->assertFileExists(
                AdminServiceProvider::views() . '/' . $name . '.php',
                "the admin renders \"{$name}\" but does not ship it",
            );
        }
    }

    public function test_the_shipped_views_are_where_the_provider_says_they_are(): void
    {
        $this->assertDirectoryExists(AdminServiceProvider::views());
    }

    /**
     * Every "admin/..." template name written in the package's source or its
     * own templates. The leading segment is what separates a template name from
     * a request path, which is spelled "/admin".
     *
     * @return list<string>
     */
    private function templateNames(): array
    {
        $names = [];

        foreach ([$this->package() . '/src', AdminServiceProvider::views()] as $root) {
            foreach ($this->filesUnder($root) as $file) {
                preg_match_all(
                    "#'(admin/[a-z0-9/_-]+)'#",
                    (string) file_get_contents($file),
                    $matches,
                );
                $names = [...$names, ...$matches[1]];
            }
        }

        return array_values(array_unique($names));
    }

    /** @return list<string> */
    private function filesUnder(string $root): array
    {
        $files = [];
        $tree = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        /** @var SplFileInfo $file */
        foreach ($tree as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function package(): string
    {
        return dirname(__DIR__, 2);
    }
}
