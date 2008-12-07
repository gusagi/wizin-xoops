include_once("{$project.rhacopath}Rhaco.php");
<rt:loop param="{$project.defineList}" var="define">
/** {$define.title} */
define("VAR_{$define.name}","{$define.name}");
</rt:loop>
Rhaco::constant("PROJECT_VERSION","{$project.version}");
Rhaco::constant("CONTEXT_PATH",Rhaco::filepath(dirname(__FILE__)));
Rhaco::constant("CONTEXT_URL","{$project.url}");
Rhaco::constant("PROJECT_PATH",Rhaco::filepath("{$project.projectPath}"));
Rhaco::constant("TEMPLATE_URL","{$project.templateUrl}");
Rhaco::constant("TEMPLATE_PATH",Rhaco::filepath("{$project.templatePath}"));
Rhaco::constant("CACHE_PATH",Rhaco::filepath("{$project.defineCachePath}"));
Rhaco::constant("TEMPLATE_CACHE",{$project.defineTemplateCache()});
Rhaco::constant("TEMPLATE_CACHE_TIME","{$project.defineTemplateCacheTime}");
Rhaco::constant("FEED_CACHE",{$project.defineFeedCache()});
Rhaco::constant("FEED_CACHE_TIME","{$project.defineFeedCacheTime}");
Rhaco::constant("LOG_FILE_LEVEL","{$project.defineLogFileLevel}");
Rhaco::constant("LOG_FILE_PATH",Rhaco::filepath("{$project.defineLogFilePath}"));
Rhaco::constant("LOG_DISP_LEVEL","{$project.defineLogDispLevel}");
Rhaco::constant("LOG_DISP_HTML",{$project.defineLogHtml()});
<rt:ifnot param="{$project.cookieExpire}" value="">
Rhaco::constant("COOKIE_EXPIRE_TIME","{$project.cookieExpire}");
</rt:ifnot>
<rt:ifnot param="{$project.sessionCache}" value="">
Rhaco::constant("SESSION_CACHE_LIMITER","{$project.sessionCache}");
</rt:ifnot>
<rt:ifnot param="{$project.sessionExpire}" value="">
Rhaco::constant("SESSION_EXPIRE_TIME","{$project.sessionExpire}");
</rt:ifnot>
<rt:loop param="{$project.defineList}" var="define">
Rhaco::constant(VAR_{$define.name},"{$define.value}");
</rt:loop>
<rt:loop param="{$project.databaseList}" var="database">
Rhaco::constant("DATABASE_{$database.class}_HOST","{$database.host}");
Rhaco::constant("DATABASE_{$database.class}_USER","{$database.user}");
Rhaco::constant("DATABASE_{$database.class}_PASSWORD","{$database.password}");
Rhaco::constant("DATABASE_{$database.class}_NAME","{$database.name}");
Rhaco::constant("DATABASE_{$database.class}_PORT","{$database.port}");
Rhaco::constant("DATABASE_{$database.class}_ENCODE","{$database.encode}");
Rhaco::constant("DATABASE_{$database.class}_TYPE","{$database.type}");
Rhaco::constant("DATABASE_{$database.class}_PREFIX","{$database.prefix}");
</rt:loop>
Rhaco::constant("APPLICATION_ID",strtoupper(preg_replace("/[\/\:\.]/","",Rhaco::constant("CONTEXT_URL"))));
Rhaco::import("resources.Message");
Rhaco::import("abbr.L");