<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Xpressengine\Plugin\Composer\Composer;
use Xpressengine\Plugin\Composer\ComposerFileWriter;
use Xpressengine\Plugin\PluginHandler;
use Xpressengine\Plugin\PluginProvider;

class PluginUpdate extends PluginCommand
{
    /**
     * The console command name.
     * php artisan plugin:install [--without-activate] <plugin name> [<version>]
     * @var string
     */
    protected $signature = 'plugin:update
                        {plugin_id : The plugin id for install}
                        {version? : The version of plugin for install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a plugin of XpressEngine';

    /**
     * Create a new controller creator command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param PluginHandler      $handler
     * @param PluginProvider     $provider
     * @param ComposerFileWriter $writer
     *
     * @return bool|null
     * @throws \Exception
     */
    public function fire(PluginHandler $handler, PluginProvider $provider, ComposerFileWriter $writer)
    {
        $this->init($handler, $provider, $writer);

        // php artisan plugin:update <plugin name> [<version>]

        $id = $this->argument('plugin_id');

        $version = $this->argument('version');

        // 플러그인이 이미 설치돼 있는지 검사
        $plugin = $handler->getPlugin($id);
        if($plugin === null) {
            // 설치되어 있지 않은 플러그인입니다.
            throw new \Exception('Plugin not found');
        }

        if(file_exists($plugin->getPath('vendor'))) {
            // 개발모드의 플러그인입니다. 개발모드의 플러그인은 업데이트 할 수 없습니다.
            throw new \Exception('The plugin is in develop mode. Can\'t update plugin in develop mode.');
        }

        // 설치가능 환경인지 검사
        // - check writable of composer.plugin.json
        if(!is_writable($composerFile = storage_path('app/composer.plugins.json'))) {
            // [$composerFile] 파일에 쓰기 권한이 없습니다. 플러그인을 설치하기 위해서는 이 파일의 쓰기 권한이 있어야 합니다.
            throw new \Exception("You have been denied permission to acccess [$composerFile] file. To update the plugin, you must have write permission to access this this file.");
        }

        // - check writable of plugins/ directory
        if(!is_writable($pluginDir = base_path('plugins'))) {
            // [$pluginDir] 디렉토리에 쓰기 권한이 없습니다. 플러그인을 설치하기 위해서는 이 디렉토리의 쓰기 권한이 있어야 합니다.
            throw new \Exception("You have been denied permission to acccess [$pluginDir] directory. To update the plugin, you must have write permissions to access this directory.");
        }

        // 자료실에서 플러그인 정보 조회
        $pluginData = $provider->find($id);

        if($pluginData === null) {
            // 설치할 플러그인[$id]을 자료실에서 찾지 못했습니다.
            throw new \Exception("Can not find the plugin(".$id.") that should be updated from the Market-place.");
        }

        $title = $pluginData->title;
        $name = $pluginData->name;

        if($version) {
            $releaseData = $provider->findRelease($id, $version);
            if($releaseData === null) {
                // 플러그인[$id]의 버전[$version]을 자료실에서 찾지 못했습니다.
                throw new \Exception("Can not find version(".$version.") of the plugin(".$id.") that should be updated from the Market-place.");
            }
        }
        $version = $version ?: $pluginData->latest_release->version;

        // 플러그인 정보 출력
        // 업데이트 플러그인 정보
        $this->warn(PHP_EOL." Information of the plugin that should be updated:");
        $this->line("  $title - $name: {$plugin->getVersion()} -> $version".PHP_EOL);

        // 안내 멘트 출력
        if($this->input->isInteractive() && $this->confirm(
                // 위 플러그인을 다운로드하고 업데이트합니다. \r\n 위 플러그인이 의존하는 다른 플러그인이 함께 다운로드 될 수 있으며, 수 분이 소요될수 있습니다.\r\n 플러그인을 업데이트하시겠습니까?"
                "The new version of above plugin will be downloaded and installed. \r\n Dependent plugins can be installed together. \r\n It may take up to a few minutes. Do you want to update the plugin?"
            ) === false) {
            return;
        }

        // - plugins require info 갱신
        $writer->reset()->cleanOperation();

        // composer.plugins.json 업데이트
        // - require에 설치할 플러그인 추가
        $writer->update($name, $version, 0)->write();

        $vendorName = PluginHandler::PLUGIN_VENDOR_NAME;

        Composer::setPackagistToken(config('xe.plugin.packagist.token'));
        Composer::setPackagistUrl(config('xe.plugin.packagist.url'));

        // composer update실행(composer update --prefer-lowest --with-dependencies xpressengine-plugin/plugin_id)
        // composer update를 실행합니다. 최대 수분이 소요될 수 있습니다.
        $this->warn('Composer update command is running.. It may take up to a few minutes.');
        $this->line(" composer update --prefer-lowest --with-dependencies $vendorName/*");
        try {
            $result = $this->runComposer([
                                             'command' => 'update',
                                             "--prefer-lowest",
                                             "--with-dependencies",
                                             '--working-dir' => base_path(),
                                             'packages' => ["$vendorName/*"]
                                         ]);
        } catch (\Exception $e) {
            ;
        }

        // composer 실행을 마쳤습니다
        $this->warn('Composer update command is finished.'.PHP_EOL);

        // composer.plugins.json 파일을 다시 읽어들인다.
        $writer->load();
        if (!isset($result) || $result !== 0) {
            $writer->set('xpressengine-plugin.operation.status', ComposerFileWriter::STATUS_FAILED);
        } else {
            $writer->set('xpressengine-plugin.operation.status', ComposerFileWriter::STATUS_SUCCESSED);
        }
        $writer->write();

        // changed plugin list 정보 출력
        $changed = $this->getChangedPlugins($writer);
        $this->printChangedPlugins($changed);

        if (array_get($changed, 'updated.'.$name) === $version) {
            // 설치 성공 문구 출력
            // $title - $name:$version 플러그인을 업데이트했습니다.
            $this->output->success("$title - $name:$version plugin is updated");
        } elseif (array_get($changed, 'updated.'.$name)) {
            $this->output->warning(
                // $name:$version 플러그인을 업데이트하였으나 다른 버전으로 업데이트되었습니다. 플러그인 간의 의존관계로 인해 다른 버전으로 업데이트되었을 가능성이 있습니다. 플러그인 간의 의존성을 살펴보시기 바랍니다.
                "The plugin[".$name."] install successed. But another version[".$version."] is installed. Because of dependencies between plugins, it is possible that they have been updated to a different version. Please check the plugin dependencies."
            );
        } elseif($plugin->getVersion() === $version) {
            $this->output->warning(
                // 동일한 버전의 플러그인이 이미 설치되어 있으므로 업데이트가 되지 않았습니다.
                "Plugin update skipped. Because the same version of plugin already was installed"
            );
        } else {
            $this->output->warning(
                // $name:$version 플러그인을 업데이트하지 못했습니다. 플러그인 간의 의존관계로 인해 업데이트가 불가능할 수도 있습니다. 플러그인 간의 의존성을 살펴보시기 바랍니다.
                "Plugin update failed. It may have failed due to dependencies between plugins. Please check the plugin dependencies."
            );
        }
    }
}
