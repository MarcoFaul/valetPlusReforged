<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Valet - <?php echo $requestedSite ?> - Not Found</title>
    <meta name="msapplication-TileColor" content="#206bc4"/>
    <meta name="theme-color" content="#206bc4"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="mobile-web-app-capable" content="yes"/>
    <meta name="HandheldFriendly" content="True"/>
    <meta name="MobileOptimized" content="320"/>
    <meta name="robots" content="noindex,nofollow,noarchive"/>
</head>
<body class="antialiased">
<style>
    <?php include 'assets/css/404.css'; ?>
    <?php include 'assets/css/tailwind.min.css'; ?>
</style>
<body class="h-screen overflow-hidden flex items-center justify-center" style="background: #edf2f7;">
<div class="font-sans bg-grey-lighter flex flex-col min-h-screen w-full">
    <div>
        <div class="bg-blue-500">
            <div class="container mx-auto">
                <div class="flex items-center">
                    <div class="text-center text-black font-medium">
                        <?php readfile($logo); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-grow container mx-auto sm:px-4 pt-6">
        <div class="bg-white border-t border-b shadow mb-6">
            <div class="flex">
                <div class="w-1/3 text-center py-4">
                    <div class="border-r">
                        <div class="text-sm uppercase text-grey tracking-wide">
                            Not found
                        </div>
                        <div class="text-gray-300er">
                            <span class="text-5xl"><?php echo $requestedSite ?></span>
                        </div>
                    </div>
                </div>
                <div class="w-1/3 text-center py-4">
                    <div class="border-r">
                        <div class="text-sm uppercase text-grey tracking-wide">
                            Current PHP version
                        </div>
                        <div class="text-gray-300er">
                            <span class="text-5xl"><?php echo phpversion() ?></span>
                        </div>
                    </div>
                </div>
                <div class="w-1/3 text-center py-4">
                    <div>
                        <div class="text-sm uppercase text-grey tracking-wide">
                            Path count
                        </div>
                        <div class="text-gray-300er">
                            <span class="text-5xl"><?php echo $valetPaths ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap -mx-4">
            <div class="w-full mb-6 lg:mb-0 lg:w-1/2 px-4 flex flex-col">
                <div class="flex-grow flex flex-col bg-white border-t border-b sm:rounded sm:border shadow overflow-hidden">
                    <div class="border-b">
                        <div class="flex justify-between px-6 -mb-px">
                            <div class="w-full items-center">
                                <h3 class="text-blue-500 py-4 font-normal text-lg">Domain link (<?php echo $siteCount ?>)</h3>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($valetConfig['paths'] as $path) : ?>
                        <?php foreach (glob(htmlspecialchars($path) . '/*', GLOB_ONLYDIR) as $site) : ?>
                            <div class="flex-grow flex px-6 py-3 text-gray-300er items-center border-b -mx-2">
                                <div class="w-3/4 items-center">
                                            <span class="text-lg">
                                                <?php if (array_key_exists(basename($site) . '.' . $valetConfig['domain'], $certificates) === true) { ?>
                                                    <a class="text-blue-600" href="https://<?php echo htmlspecialchars(basename($site) . '.' . $valetConfig['domain']); ?>"><?php echo htmlspecialchars(basename($site) . '.' . $valetConfig['domain']); ?></a>
                                                <?php } else { ?>
                                                    <a class="text-blue-600" href="http://<?php echo htmlspecialchars(basename($site) . '.' . $valetConfig['domain']); ?>"><?php echo htmlspecialchars(basename($site) . '.' . $valetConfig['domain']); ?></a>
                                                <?php } ?>
                                            </span>
                                </div>
                                <div class=" w-1/4 items-center right-0">
                                    <?php if (array_key_exists(basename($site) . '.' . $valetConfig['domain'], $certificates) === true) { ?>
                                        <?php readfile($ssl); ?>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="w-full mb-6 lg:mb-0 lg:w-1/2 px-4 flex flex-col">
                <div class="flex flex-col bg-white border-t border-b sm:rounded sm:border shadow overflow-hidden">
                    <div class="border-b">
                        <div class="flex justify-between px-6 -mb-px">
                            <h3 class="text-blue-500 py-4 font-normal text-lg">Registered paths (<?php echo $valetPaths ?>)</h3>
                        </div>
                    </div>
                    <?php foreach ($valetConfig['paths'] as $path) : ?>
                        <div class="flex-grow flex px-6 py-3 text-gray-300er items-center border-b -mx-2">
                            <div class="w-dull items-center">
                                <span class="text-lg"><?php echo htmlspecialchars($path); ?></span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-grow container mx-auto sm:px-4 pt-6">
        <div class="bg-white border-t border-b sm:border-l sm:border-r sm:rounded shadow mb-6">
            <div class="px-6 py-4 border-b">
                <div class="text-center text-grey">
                    Configuration
                </div>
            </div>
            <div class="flex">

                <?php foreach ($valetCustomConfig as $name => $config) : ?>
                    <div class="w-1/3 text-center py-4">
                        <div class="border-r">
                            <div class="text-sm text-ul uppercase text-grey tracking-wide">
                                <u><?php echo $name ?></u>
                            </div>
                            <div class="text-gray-300er">
                                <?php foreach ($config as $enabled => $value) : ?>
                                    <span class="text-1xl"><?php echo $enabled ?>: <b><?php echo (int)$value ?></b> </span><br/>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="bg-white border-t">
        <div class="container mx-auto px-4">
            <div class="md:flex justify-between items-center text-sm">
                <div class="relative w-full md:flex md:flex-row-reverse items-center py-4">
                    <div class="text-gray-600 absolute right-0 text-center md:mr-4">Copyright &copy; <?php echo date('Y'); ?> Valet+ reforged. All rights reserved.</div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</body>
</html>
