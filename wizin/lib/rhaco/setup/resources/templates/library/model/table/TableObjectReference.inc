<rt:loop param="{$table.columnList}" var="column">
<rt:loop param="{$column.dependList}" var="depend">
	function setDepend{$depend[0].pluralMethod}($value){
		$this->depend{$depend[0].pluralMethod} = $value;
	}
	function getDepend{$depend[0].pluralMethod}(){
		return $this->depend{$depend[0].pluralMethod};
	}
</rt:loop>
</rt:loop>
<rt:loop param="{$table.mapList}" var="map">
	function set{$map[0].pluralMethod}($value){
		$this->{$map[0].plural} = $value;
	}
	function get{$map[0].pluralMethod}(){
		return $this->{$map[0].plural};
	}
</rt:loop>