<?php

namespace App\Command;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:minify', description: 'Minify assets')]
class AssetsCompileCommand extends Command
{
    private string $path;
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->path = $parameterBag->get('project_dir');
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // test to run compile asset before minify
//        $output->writeln('commande compil des assets');
//        $greetInput = new ArrayInput([
//            'command' => 'asset-map:compile'
//        ]);
//        $this->getApplication()->doRun($greetInput, $output);

        $output->writeln(['Minify css and js', '============', '',]);
        $manifest = json_decode(file_get_contents($this->path . '/public/assets/manifest.json'), true);

        foreach ($manifest as $key => $file) {
            $info = new SplFileInfo($file);
            if ('js' === $info->getExtension()) {
                $minifier = new JS($this->path . '/public' . $file);
                $minifier->minify($this->path . '/public' . $file);
            } elseif ('styles/app.scss' === $key) {
                $minifier = new CSS($this->path . '/public' . $file);
                $minifier->minify($this->path . '/public' . $file);
            }
        }

        // outputs a message followed by a "\n"
        $output->writeln('CSS and JS minify');
        // outputs a message without adding a "\n" at the end of the line
        $output->write('it\'s ok');
        return Command::SUCCESS;
    }
}