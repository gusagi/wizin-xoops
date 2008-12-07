<rt:loop param="{$table.extraList}" var="column">
	var ${$column.var};
</rt:loop>
<rt:loop param="{$table.columnList}" var="column">
<rt:if param="{$column.isReference()}">
	var $fact{$column.method};
</rt:if>
<rt:loop param="{$column.dependList}" var="depend">
	var $depend{$depend[0].pluralMethod};
</rt:loop>
</rt:loop>
<rt:loop param="{$table.mapList}" var="map">
	var ${$map[0].plural};
</rt:loop>
