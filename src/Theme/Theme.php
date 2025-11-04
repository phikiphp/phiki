<?php

namespace Phiki\Theme;

use Phiki\Contracts\ThemeRepositoryInterface;

enum Theme: string
{
    case Andromeeda = 'andromeeda';
    case AuroraX = 'aurora-x';
    case AyuDark = 'ayu-dark';
    case CatppuccinFrappe = 'catppuccin-frappe';
    case CatppuccinLatte = 'catppuccin-latte';
    case CatppuccinMacchiato = 'catppuccin-macchiato';
    case CatppuccinMocha = 'catppuccin-mocha';
    case DarkPlus = 'dark-plus';
    case Dracula = 'dracula';
    case DraculaSoft = 'dracula-soft';
    case EverforestDark = 'everforest-dark';
    case EverforestLight = 'everforest-light';
    case GithubDark = 'github-dark';
    case GithubDarkDefault = 'github-dark-default';
    case GithubDarkDimmed = 'github-dark-dimmed';
    case GithubDarkHighContrast = 'github-dark-high-contrast';
    case GithubLight = 'github-light';
    case GithubLightDefault = 'github-light-default';
    case GithubLightHighContrast = 'github-light-high-contrast';
    case GruvboxDarkHard = 'gruvbox-dark-hard';
    case GruvboxDarkMedium = 'gruvbox-dark-medium';
    case GruvboxDarkSoft = 'gruvbox-dark-soft';
    case GruvboxLightHard = 'gruvbox-light-hard';
    case GruvboxLightMedium = 'gruvbox-light-medium';
    case GruvboxLightSoft = 'gruvbox-light-soft';
    case Houston = 'houston';
    case KanagawaDragon = 'kanagawa-dragon';
    case KanagawaLotus = 'kanagawa-lotus';
    case KanagawaWave = 'kanagawa-wave';
    case Laserwave = 'laserwave';
    case LightPlus = 'light-plus';
    case MaterialTheme = 'material-theme';
    case MaterialThemeDarker = 'material-theme-darker';
    case MaterialThemeLighter = 'material-theme-lighter';
    case MaterialThemeOcean = 'material-theme-ocean';
    case MaterialThemePalenight = 'material-theme-palenight';
    case MinDark = 'min-dark';
    case MinLight = 'min-light';
    case Monokai = 'monokai';
    case NightOwl = 'night-owl';
    case Nord = 'nord';
    case OneDarkPro = 'one-dark-pro';
    case OneLight = 'one-light';
    case Plastic = 'plastic';
    case Poimandres = 'poimandres';
    case Red = 'red';
    case RosePine = 'rose-pine';
    case RosePineDawn = 'rose-pine-dawn';
    case RosePineMoon = 'rose-pine-moon';
    case SlackDark = 'slack-dark';
    case SlackOchin = 'slack-ochin';
    case SnazzyLight = 'snazzy-light';
    case SolarizedDark = 'solarized-dark';
    case SolarizedLight = 'solarized-light';
    case Synthwave_84 = 'synthwave-84';
    case TokyoNight = 'tokyo-night';
    case Vesper = 'vesper';
    case VitesseBlack = 'vitesse-black';
    case VitesseDark = 'vitesse-dark';
    case VitesseLight = 'vitesse-light';

    public function path(): string
    {
        return match ($this) {
            default => __DIR__."/../../resources/themes/{$this->value}.json",
        };
    }

    public function toParsedTheme(ThemeRepositoryInterface $repository): ParsedTheme
    {
        return $repository->get($this->value);
    }

    public static function parse(array $theme): ParsedTheme
    {
        return (new ThemeParser)->parse($theme);
    }
}
