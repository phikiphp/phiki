<?php

namespace Phiki\Theme;

use Phiki\Contracts\ThemeRepositoryInterface;

enum Theme: string
{
    case OneDarkPro = "one-dark-pro";
case SolarizedLight = "solarized-light";
case VitesseBlack = "vitesse-black";
case GithubLightDefault = "github-light-default";
case SlackDark = "slack-dark";
case EverforestDark = "everforest-dark";
case RosePineMoon = "rose-pine-moon";
case EverforestLight = "everforest-light";
case Laserwave = "laserwave";
case GithubLightHighContrast = "github-light-high-contrast";
case CatppuccinMocha = "catppuccin-mocha";
case Red = "red";
case MaterialThemeLighter = "material-theme-lighter";
case OneLight = "one-light";
case AuroraX = "aurora-x";
case TokyoNight = "tokyo-night";
case CatppuccinMacchiato = "catppuccin-macchiato";
case GithubDark = "github-dark";
case RosePineDawn = "rose-pine-dawn";
case Poimandres = "poimandres";
case GithubDarkHighContrast = "github-dark-high-contrast";
case MaterialTheme = "material-theme";
case Dracula = "dracula";
case GithubDarkDefault = "github-dark-default";
case GithubDarkDimmed = "github-dark-dimmed";
case RosePine = "rose-pine";
case KanagawaLotus = "kanagawa-lotus";
case KanagawaDragon = "kanagawa-dragon";
case DarkPlus = "dark-plus";
case AyuDark = "ayu-dark";
case MinDark = "min-dark";
case Monokai = "monokai";
case Nord = "nord";
case CatppuccinFrappe = "catppuccin-frappe";
case GithubLight = "github-light";
case DraculaSoft = "dracula-soft";
case Synthwave84 = "synthwave-84";
case VitesseDark = "vitesse-dark";
case Andromeeda = "andromeeda";
case LightPlus = "light-plus";
case SlackOchin = "slack-ochin";
case SolarizedDark = "solarized-dark";
case MaterialThemeOcean = "material-theme-ocean";
case VitesseLight = "vitesse-light";
case Vesper = "vesper";
case KanagawaWave = "kanagawa-wave";
case Plastic = "plastic";
case MaterialThemeDarker = "material-theme-darker";
case NightOwl = "night-owl";
case CatppuccinLatte = "catppuccin-latte";
case MinLight = "min-light";
case SnazzyLight = "snazzy-light";
case Houston = "houston";
case MaterialThemePalenight = "material-theme-palenight";

    public function toParsedTheme(ThemeRepositoryInterface $repository): ParsedTheme
    {
        return $repository->get($this->value);
    }
}