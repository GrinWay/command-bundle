grinway/command-bundle
========

# Description


This bundle provides:
| Class name | Description |
| ------------- | ------------- |
| [AbstractCommand](https://github.com/GrinWay/command-bundle/blob/main/src/Command/AbstractCommand.php) | The basic class which realizes the Symfony Command class |
| [Traits](https://github.com/GrinWay/command-bundle/tree/main/src/Trait) | For users' options and abstactions |

## AbstractCommand

- See the "CONSTANTS CHANGE ME" section.
- See the "PUBLIC API" section for your services.
- See the "API" and "YOU CAN OVERRIDE IT" section for your extended commands.
- See the "REALIZED ABSTRACT" to make parent::METHOD() and add something new in the basic realization.

### Translations

For several functions you can add your own translations:

| Functions API |
| ------------- |
| AbstractCommand::getTranslator() |
| AbstractCommand::getIo() |
| AbstractCommand::ioDump() |
| AbstractCommand::getProgressBar() |
| AbstractCommand::getDefaultTableStyle() |
| AbstractCommand::getTable() |
| AbstractCommand::getCloneTable() |
| AbstractCommand::getFormatter() |
| AbstractCommand::dd() |
| AbstractCommand::ddFinder() |
| AbstractCommand::configureOption() |
| AbstractCommand::configureArgument() |
| AbstractCommand::initializeOption() |
| AbstractCommand::initializeArgument() |
| AbstractCommand::getInfoDescription() |
| AbstractCommand::isOk() |
| AbstractCommand::exit() |
| AbstractCommand::shutdown() |


For the "ru" locale add your translations into the directory:
`%kernel.project_dir%/translations/GrinWay/Command/messages.ru.yaml`

### Progress Bar

Into your command your can use [Progress Bar](https://symfony.com/doc/current/components/console/helpers/progressbar.html)

When you have the known max steps use into your command `$this->setMaxSteps()` method:
```php
$this->progressBar->setMaxSteps(KNOWN_MAX_STEPS);
$this->progressBar->start();
```

## Traits

| Trait | Description |
| ------------- | ------------- |
| [AskAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/AskAbleTrait.php) | Adds option for the programm which allows user to choose whether to ask him or not. |
| [DepthAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/DepthAbleTrait.php) | Adds option for the programm which allows user to indicate depth. |
| [DumpInfoAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/DumpInfoAbleTrait.php) | Adds option for the programm which allows user to dump information or not. [\GrinWay\Service\Service\DumpInfoService::dumpInfo()](https://github.com/GrinWay/service-bundle/blob/main/src/Service/DumpInfoService.php) from the other bundle relies on `DepthAbleTrait::isDumpInfo()` method before the dump but it's not crucial. |
| [MakeLockAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/MakeLockAbleTrait.php) | Adds option for the programm which allows user to choose whether to lock or not. |
| [MoveAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/MoveAbleTrait.php) | Adds option for the programm which allows user to choose whether to move or not. |
| [OverrideAbleTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/OverrideAbleTrait.php) | Adds option for the programm which allows user to choose whether to override or not. |
| [AbstractConstructedFromToCommandTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/AbstractConstructedFromToCommandTrait.php) | Abstraction for doing something with the constructed absolute paths from and to. |
| [AbstractPatternAbleCommandTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/AbstractPatternAbleCommandTrait.php) | Abstraction for processing the passed pattern. |
| [AbstractConvertExtCommandTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/AbstractConvertExtCommandTrait.php) | Abstraction for converting files into another extension. |
| [AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait](https://github.com/GrinWay/command-bundle/blob/main/src/Trait/PatternAbleInstance/AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait.php) | It parses three parts of an expression with all the possible combinations |

## Command

| Command | Description |
| ------------- | ------------- |
| [AbstractCommand](https://github.com/GrinWay/command-bundle/tree/main/src/Command/AbstractCommand.php) | Ready to extending abstract command |
| [AbstractDisplayCommand](https://github.com/GrinWay/command-bundle/tree/main/src/Command/AbstractDisplayCommand.php) | Ready to extending abstract display command |
| [PdfCommand](https://github.com/GrinWay/command-bundle/tree/main/src/Command/PdfCommand.php) | PDF converter (READY TO USE) |
| [NowDateCommand](https://github.com/GrinWay/command-bundle/tree/main/src/Command/NowDateCommand.php) | Assign updating and creation date and time of the file (READY TO USE) |
| [ShowCommand](https://github.com/GrinWay/command-bundle/tree/main/src/Command/ShowCommand.php) | Show not empty directories by path (READY TO USE) |

### Use various IODumpers with AbstractCommand::ioDump()

| IODumpers |
| ------------- |
| [CautionIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/CautionIODumper.php) |
| [CommentIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/CommentIODumper.php) |
| [DefaultIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/DefaultIODumper.php) |
| [ErrorIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/ErrorIODumper.php) |
| [FormattedIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/FormattedIODumper.php) |
| [InfoIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/InfoIODumper.php) |
| [ListingIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/ListingIODumper.php) |
| [NoteIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/NoteIODumper.php) |
| [SectionIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/SectionIODumper.php) |
| [SuccessIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/SuccessIODumper.php) |
| [TextIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/TextIODumper.php) |
| [TitleIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/TitleIODumper.php) |
| [WarningIODumper](https://github.com/GrinWay/command-bundle/blob/main/src/Contracts/IO/WarningIODumper.php) |

### Initial state of the AbstractCommand

| AbstractCommand state | Description |
| ------------- | ------------- |
| $this->grinWayCommandInitialCwd | `\getcwd()` when the command starts |

# Installation


### Step 1: Download the bundle

[Before git clone](https://github.com/GrinWay/docs/blob/main/docs/bundles_grin_symfony%20mkdir.md)

```console
git clone "https://github.com/GrinWay/command-bundle.git"
```

```console
git clone "https://github.com/GrinWay/service-bundle.git"
```

### Step 2: Require the bundle

In your `%kernel.project_dir%/composer.json`

```json
"require": {
	"grinway/command-bundle": "VERSION"
},
"repositories": [
	{
		"type": "path",
		"url": "./bundles/grinway/command-bundle"
	},
	{
		"type": "path",
		"url": "./bundles/grinway/service-bundle"
	}
]
```

Open your console into your main project directory and execute:

```console
composer require "grinway/command-bundle"
```

[Binds](https://github.com/GrinWay/docs/blob/main/docs/borrow-services.yaml-section.md)

**Monolog customization**

In your `%kernel.project_dir%/config/packages/monolog.yaml`

```yaml
###> TODO: REALIZE IT IN YOUR monolog.yaml ###
when@dev:
    monolog:
        handlers:
            grin_way_command.dev_logger:
                type:           rotating_file
                max_files:      1
                path:           "%kernel.logs_dir%/grin_way_command_%kernel.environment%.log"
                level:          debug
                channels:       ["grin_way_command.dev_logger"]
###< TODO: REALIZE IT IN YOUR monolog.yaml ###
```

### Step 3: Extend the AbstractCommand in your Command