<?php /* HypPieChart - GD Library pie charting system */

class HypPieChart{
	private $type,$width,$height,$arc_width,$arc_height,$arc_thickness,$background,$colors_array;
	public function __construct($args = ''){
		$defaults = array('type' => 'png','width' => 300,'height' => 300,'arc_width' => 200,'arc_height' => 100,'arc_thickness' => 10,'background' => 'FFFFFF','testing' => false);
		//Store properties
		$properties = $this->parse_args($args,$defaults);
		extract($properties);
		foreach($properties as $key => $value){ $this->{$key} = $value; }
	}
	
	/* ~~~~~~~~~~~~~~~~~~~ Class Methods ~~~~~~~~~~~~~~~~~~~ */
	
	private function allocateColor($image,$hex){
		$hex = $this->hex_fix($hex);
		$int = hexdec($hex);
		return imagecolorallocate($image,0xFF & ($int >> 0x10),0xFF & ($int >> 0x8),0xFF & $int);
	}
	private function check_for_errors(){
    	if(count($this->pieces_array) > (count($this->colors_array) -1)){ echo 'You must assign a color for each piece using set_colors()'; exit;}
  	}
	private function hex_fix($hex){
		if(strlen($hex) == 3) if(preg_match('/^([0-9a-f])([0-9a-f])([0-9a-f])/i',$hex)) $hex = preg_replace('/^([0-9a-f])([0-9a-f])([0-9a-f])/i','\\1\\1\\2\\2\\3\\3',$hex);
		return $hex;
	}
	private function hex_shift($hex,$percent = 10,$shift_dir = '-'){
		$RGB_values = array();
		$new_hex = null;
		$hex = $this->hex_fix($hex);
		$RGB_values['R'] = hexdec($hex{0}.$hex{1});
		$RGB_values['G'] = hexdec($hex{2}.$hex{3});
		$RGB_values['B'] = hexdec($hex{4}.$hex{5});
		foreach($RGB_values as $c => $v){
			if($shift_dir == '+') $amount = round(((255 - $v) / 100) * $percent) + $v;
			else $amount = $v - round(($v / 100) * $percent);
			$new_hex .= $current_value = (strlen($decimal_to_hex = dechex($amount)) < 2) ? '0' . $decimal_to_hex : $decimal_to_hex;
		}
		return $new_hex;
	}
	private function parse_args($args,$defaults = ''){
    	if(is_object($args)) $r = get_object_vars($args);
    	elseif(is_array($args)) $r =& $args;
       	else $this->parse_string($args,$r);
        if(is_array($defaults)) return array_merge($defaults,$r);
        return $r;
    }
	private function parse_string($string, &$array){
    	parse_str($string,$array);
		if(get_magic_quotes_gpc()) $array = stripslashes_deep($array);
     	return $array;
  	}
	
	/* ~~~~~~~~~~~~~~~~~~~ Accessors ~~~~~~~~~~~~~~~~~~~ */
	
	public function set_colors($colors_array){
		//Array as 'white' => 'ffffff'
		$this->colors_array = $colors_array;
		$this->colors_array['background'] = $this->background;
	}
	public function hex_change($hex,$percent = 10,$shift_dir = '-'){
		return $this->hex_shift($hex,$percent,$shift_dir);
	}
	public function set_pieces($pieces_array){
		//Array as 'name' => 10  - 10 is the percent 3.6 = 1% of 360
		$this->pieces_array = $pieces_array;
	}
	
	/* ~~~~~~~~~~~~~~~~~~~ Build ~~~~~~~~~~~~~~~~~~~ */
	public function build(){
		$this->check_for_errors();
		$image = imagecreatetruecolor($this->width,$this->height);
		$arc_width = $this->arc_width;
		$arc_height = $this->arc_height;
		$arc_center_x = $this->width / 2;
		$arc_center_y = $this->height / 2;
		$arc_side_i = $arc_center_y + $this->arc_thickness;
		//Get Colors
		foreach($this->colors_array as $key => $value){
			$color_index[] = $key;
			${$key} = $this->allocateColor($image,$value);
			$temp = $key . '_2';
			$$temp = $this->allocateColor($image,$this->hex_shift($value,24));
		}
		//Background
		imagefilltoborder($image,0,0,$background,$background);
		//Draw Side	
		for($i = $arc_side_i; $i > $arc_center_y; $i--){
			$ii = 0; $piece_start_deg = 0; $piece_end_deg = 0;
			foreach($this->pieces_array as $key => $value){
				$color = $color_index[$ii] . '_2';
				$display_color = ${$color};
				$piece_deg = ($value / 100) * 360;
				$piece_end_deg += $piece_deg;
				imagefilledarc($image,$arc_center_x,$i,$arc_width,$arc_height,$piece_start_deg,$piece_end_deg,$display_color,IMG_ARC_PIE);
				$piece_start_deg = $piece_end_deg;
				$ii++;
			}
		}
		//Draw Top
		$ii = 0; $piece_start_deg = 0; $piece_end_deg = 0;
		foreach($this->pieces_array as $key => $value){
			$color = $color_index[$ii];
			$display_color = ${$color};
			$piece_deg = ($value / 100) * 360;
			$piece_end_deg += $piece_deg;
			imagefilledarc($image,$arc_center_x,$arc_center_y,$arc_width,$arc_height,$piece_start_deg,$piece_end_deg,$display_color,IMG_ARC_PIE);
			$piece_start_deg = $piece_end_deg;
			$ii++;
		}
		
		
		// Write the string at the top left
		imagestring($image, 5, 0, 0, 'Hello world!', $navy);
		
		//Don't send header if testing
		if($this->testing) return;
		
		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}	
}?>