<?php
namespace App\Services;
/** Exact signed decimal addition and rounding, without float conversion or optional PHP extensions. */
final class PosReportDecimal
{
 public static function valid($v):bool{return is_string($v)&&preg_match('/^-?\d{1,29}(?:\.\d{1,28})?$/D',$v);}
 private static function parts(string $v):array {
  if(!self::valid($v))throw new \InvalidArgumentException('Invalid report decimal.');
  $negative=str_starts_with($v,'-');$v=ltrim($v,'-');$p=explode('.',$v);$digits=ltrim($p[0].str_pad($p[1]??'',28,'0'),'0');return [$digits===''?false:$negative,$digits?:'0'];
 }
 private static function compare(string $a,string $b):int{return strlen($a)<=>strlen($b)?:strcmp($a,$b);}
 private static function unsigned(string $a,string $b,bool $subtract):string {
  $length=max(strlen($a),strlen($b));$a=str_pad($a,$length,'0',STR_PAD_LEFT);$b=str_pad($b,$length,'0',STR_PAD_LEFT);$carry=0;$out='';
  for($i=$length-1;$i>=0;$i--){$n=(int)$a[$i]+($subtract?-(int)$b[$i]:(int)$b[$i])+$carry;if($subtract){$carry=$n<0?-1:0;if($n<0)$n+=10;}else{$carry=intdiv($n,10);$n%=10;}$out=(string)$n.$out;}
  if(!$subtract&&$carry)$out=$carry.$out;return ltrim($out,'0')?:'0';
 }
 private static function plain(bool $negative,string $digits):string {
  $digits=str_pad($digits,29,'0',STR_PAD_LEFT);$v=ltrim(substr($digits,0,-28),'0')?:'0';$fraction=rtrim(substr($digits,-28),'0');return ($negative&&($v!=='0'||$fraction!=='')?'-':'').$v.($fraction!==''?'.'.$fraction:'');
 }
 public static function add(string $a,string $b):string {
  [$na,$da]=self::parts($a);[$nb,$db]=self::parts($b);
  if($na===$nb)return self::plain($na,self::unsigned($da,$db,false));$compare=self::compare($da,$db);
  return $compare>=0?self::plain($na,self::unsigned($da,$db,true)):self::plain($nb,self::unsigned($db,$da,true));
 }
 public static function compareValues(string $a,string $b):int { $difference=self::sub($a,$b);return $difference==='0'?0:(str_starts_with($difference,'-')?-1:1); }
 public static function negate(string $v):string{return str_starts_with($v,'-')?substr($v,1):($v==='0'?'0':'-'.$v);}
 public static function sub(string $a,string $b):string{return self::add($a,self::negate($b));}
 public static function format(string $value,int $places,bool $trim=false,bool $group=true):string {
  [$negative,$digits]=self::parts($value);$digits=str_pad($digits,29,'0',STR_PAD_LEFT);$discard=28-$places;
  if($discard>0){$round=(int)$digits[strlen($digits)-$discard]>=5;$digits=substr($digits,0,-$discard);if($round)$digits=self::unsigned($digits,'1',false);}
  $digits=str_pad($digits,$places+1,'0',STR_PAD_LEFT);$integer=$places?substr($digits,0,-$places):$digits;$fraction=$places?substr($digits,-$places):'';if($trim)$fraction=rtrim($fraction,'0');
  if($group)$integer=preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $integer)??$integer;
  return ($negative&&trim($digits,'0')!==''?'-':'').$integer.($fraction!==''?'.'.$fraction:'');
 }
}
