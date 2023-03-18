<?php

class Config {
    // jumlah kromosom per individu
    public static 
        $jumlah_individu = 5,
        $key = [
            'hari' => 0,
            'mapel' => 1,
            'guru' => 2,
            'ruang' => 3,
            'kelas' => 4,
            'jawal' => 5,
            'durasi' => 6
        ],
        $total_jp = 10,
        $selection_rate = 90,
        $mutation_rate = 1; // precent
}

class Data {
    public static $MASTER_HARI = [1,2,3,4,5],
    $MASTER_MAPEL = [1,2,3,4,5,6,7],
    $MASTER_GURU = [1,2,3,4,5,6,7,8,9,11],
    $MASTER_RUANG = [1,2,3,4,5,6,7,8,9,10,11,12,13],
    $MASTER_KELAS = [1,2,3,4,5],
    $MASTER_JAWAL = [1,2,3,4,5,6,7,8,9,10],
    $MASTER_DURASI = [1,2,3,4,5,6,7,8,9,10];
}

class Kromosom {
    
    public $gens = [];
    /**
     * desc: fungsi untuk shuffle array dan return value bukan key
     */
    private function shuffle(Array $arr)
    {
        return $arr[array_rand($arr)];
    }

    /**
     * desc: membuat gen random
     */
    public function initialize()
    {
        $this->gens = [
            $this->shuffle(Data::$MASTER_HARI),
            $this->shuffle(Data::$MASTER_MAPEL),
            $this->shuffle(Data::$MASTER_GURU),
            $this->shuffle(Data::$MASTER_RUANG),
            $this->shuffle(Data::$MASTER_KELAS),
            $this->shuffle(Data::$MASTER_JAWAL),
            $this->shuffle(Data::$MASTER_DURASI),
        ];
    }    

    public function mutate()
    {
        $_key = array_rand($this->gens);
        $_gens = $this->gens;
        switch ($_key) {
            case 0:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_HARI);
                break;
            case 1:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_MAPEL);
                break;
            case 2:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_GURU);
                break;
            case 3:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_RUANG);
                break;
            case 4:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_KELAS);
                break;
            case 5:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_JAWAL);
                break;
            case 5:
                $_gens[$_key] = $this->shuffle(Data::$MASTER_DURASI);
                break;
        }
        $this->gens = $_gens;
    }

}

class Individu {

    public $kromosoms = [];
    public $fitness = 0;
    public $id = ""; 

    public static function jumlah_kromosom()
    {
        return count(Data::$MASTER_HARI) * count(Data::$MASTER_KELAS) * Config::$total_jp;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function __construct()
    {
        $this->id = $this->generateRandomString(5);
    }

    public function generateKromosom($jumlah_kromosom)
    {
        for ($i=0; $i < $jumlah_kromosom; $i++) { 
            $newKromosom = new Kromosom();
            $newKromosom->initialize();
            $this->kromosoms[] = $newKromosom;
        }
    }

    public function customKromosom(array $kromosoms)
    {
        print_r('Kromosom change on Individu #'. $this->id.PHP_EOL);
        $this->kromosoms = $kromosoms;
    }

}


class GA {

    public $population = [];
    public $best = 10;
    public $bestIndividual;

    public function __construct()
    {
        for ($i=0; $i < Config::$jumlah_individu; $i++) { 
            $indv = new Individu();
            $indv->generateKromosom(Individu::jumlah_kromosom());
            $this->population[] = $indv;
        }
    }
    

    private function no_duplicate($input_array){
        return count($input_array) === count(array_flip($input_array));
    }

    private function checkHari($individu)
    {
        $tmp_hari = [];
        $kromosoms = $individu->kromosoms;
        for ($i=0; $i < count($kromosoms); $i++) { 
            $kromosom = $kromosoms[$i];
            if (isset($tmp_hari[$kromosom->gens[Config::$key['hari']]])) {
                $tmp_hari[$kromosom->gens[Config::$key['hari']]]++;                
            }else{
                $tmp_hari[$kromosom->gens[Config::$key['hari']]] = 1;                
            }
        }

        // print_r($tmp_hari);
        $penalty = 0;
        foreach ($tmp_hari as $key => $value) {
            if($value !== (count(Data::$MASTER_KELAS) * Config::$total_jp)){
                $penalty += 0.5;
            }
        }
        // print_r("Individu #". $individu->id ." penalty => ". $penalty . PHP_EOL);
        return $penalty;

    }

    private function checkDurasi($individu)
    {
        $_tmp_data = [];
        $kromosoms = $individu->kromosoms;
        for ($i=0; $i < count($kromosoms); $i++) { 
            $kromosom = $kromosoms[$i];
            $_gen_hari = $kromosom->gens[Config::$key['hari']];
            $_gen_durasi = $kromosom->gens[Config::$key['durasi']];
            if(!isset($_tmp_data[$_gen_hari])){
                $_tmp_data[$_gen_hari] = 0;
            }
            $_tmp_data[$_gen_hari] += (Int) $_gen_durasi;
        }

        $_penalty = 0;
        // print_r($_tmp_data);
        foreach ($_tmp_data as $hari => $durasi) {
            if ((Int) $durasi > Config::$total_jp * count(Data::$MASTER_HARI) * count(Data::$MASTER_KELAS)) {
                $_penalty += 0.5;
            }
        }
        return $_penalty;

    }

    public function fitness(array $population)
    {
        for ($i=0; $i < count($population); $i++) { 
            $individu = $population[$i];
            $pinaltyPoint = 0;

            $pinaltyPoint += $this->checkHari($individu);
            $pinaltyPoint += $this->checkDurasi($individu);

            $individu->fitness = ($pinaltyPoint);
        }
    }

    public function selection()
    {
        $max_selection = floor(Config::$selection_rate / 100 * Config::$jumlah_individu);
        $tmp_array = [];
        for ($i=0; $i < count($this->population); $i++) { 
            $individu = $this->population[$i];
            $tmp_array[$i] = $individu->fitness;
        }
        asort($tmp_array);
		$debug = $tmp_array;
        $tmp_array = array_slice($tmp_array, 0, $max_selection, true);
        print_r($tmp_array);
        // print_r('max_selection ' . implode(',',array_keys(array_slice($tmp_array, 0, $max_selection, true))) . PHP_EOL);
        $tmp_array = array_keys($tmp_array);

        $tmp_population = $this->population;
        // print_r("julmah => ".count($tmp_population));
        foreach ( $tmp_population as $key => $individu) {
            // print_r('stay ' . implode(' ,',$tmp_array) . PHP_EOL);
			if(!in_array($key, $tmp_array)){
                // print_r("unset " . $key . PHP_EOL);
                // unset
                array_splice($tmp_population, $key, 1);
			}
        }
        // print('after unset count ' . count($tmp_population) . PHP_EOL);
        // print_r($tmp_population);
        $this->population = $tmp_population;
        print_r('[SELECTION] Population after selection '.count($this->population).PHP_EOL);
    }

    public function crossOver()
    {
        $new_population = array();
		for($i = 0; $i < Config::$jumlah_individu; $i++){
			// get random parents
			$a = $this->population[array_rand($this->population, 1)];
			$b = $this->population[array_rand($this->population, 1)];
			
			$a = $a->kromosoms;
			$b = $b->kromosoms;

            $slice_index = rand(0, Individu::jumlah_kromosom() -1);
            
            $offA = array_slice($a, $slice_index);
            $childA = array_reverse(array_slice(array_reverse($a), (Individu::jumlah_kromosom()) - $slice_index));
            
            $offB = array_slice($b, $slice_index);
            $childB = array_reverse(array_slice(array_reverse($b), (Individu::jumlah_kromosom()) - $slice_index));
            
            // print_r("new Indiviual, kromosom created => ".count($childA)." + ".count($offA).PHP_EOL);
            // print_r("new Indiviual, kromosom created => ".count($childB)." + ".count($offB).PHP_EOL);
            $childA = array_merge($childA, $offB);
            $childB = array_merge($childB, $offA);

            $inv1 = new Individu();
            $inv2 = new Individu();
            $inv1->customKromosom($childA);
            $inv2->customKromosom($childB);
            $new_population[] = $inv1;
            $new_population[] = $inv2;
			
            // // print(implode($a) . " x " .implode($b) . PHP_EOL);
			// // get random chromosome from parents
			// $child = [];
			// for($j = 0; $j < count($a); $j++){
			// 	$child[] = rand(0, 1) >= 0.7 ? $a[$j] : $b[$j];
			// }
            // $indv = new Individu();
            // $indv->customKromosom($child);
			// // $new_population[] = $indv;
            // $this->population[] = $indv;
			// // $new_population[] = array(
			// // 	'chromosome' => $child,
			// // 	'fitness' => 0,
			// // );
		}

        $this->fitness($new_population);
        for ($i=0; $i < count($new_population); $i++) { 
            $individual = $new_population[$i];
            if($individual->fitness < $this->best){
                $this->population[] = $individual;
            }
        }

        print_r("[CROSS OVER] Population after crossOver ".count($this->population).PHP_EOL);

    }

    public function mutation(){
		foreach($this->population as $k => $v){
            $newKromosoms = [];
            $oldKromosoms = $v->kromosoms;
            foreach ($v->kromosoms as $key => $kromosom) {
                // get mutation chance
                $is_mutating = (rand(0, 1000) / 1000 * 100) <= Config::$mutation_rate;
                if(!$is_mutating) continue;
                // var_dump($k.gettype($kromosom));
                $kromosom->mutate();
            }
            $oldIndividu = new Individu();
            $oldIndividu->customKromosom($oldKromosoms);
            $this->fitness([$v, $oldIndividu]);

            // mutasi tidak memenuhi syarat
            if($v->fitness > $oldIndividu->fitness){
                $v->customKromosom($oldIndividu->kromosoms);
            }

		}
	}

    public function getBest(){
		// $this->fitness();
		$best_i = 0;
		$tmp = 10;
		foreach($this->population as $k => $v){
			if($v->fitness < $this->best){
				$tmp = $v->fitness;
				$best_i = $k;
                $this->best = $this->population[$best_i]->fitness;
                $this->bestIndividual = $this->population[$best_i];
			}
		}
	}

    public function run()
    {
        $i = 0;
        while ($this->best != 0) {
            // usleep(1000000);
            print_r("[GEN #".$i."][FITNESS COUNTING] happened".PHP_EOL);
            $this->fitness($this->population);
            $this->getBest();
            foreach ($this->population as $key => $individual) {
                foreach ($individual->kromosoms as $ckey => $c) {
                    // print_r("GEN #".$i." Kromosom #".$ckey. ' ' . implode('|',$c->gens) . PHP_EOL);
                }
                print_r("[GEN #".$i."] Individu #".$individual->id.' fitness = '. $individual->fitness . PHP_EOL);
            }
            print_r("[GEN #".$i."][SELECTION]".PHP_EOL);
            $this->selection();
            if ($this->best == 0) {
                break;
            }

            print_r("[GEN #".$i."][CROSSOVER]".PHP_EOL);
            $this->crossOver();
            
            print_r("[GEN #".$i."][SELECTION]".PHP_EOL);
            $this->selection();
            if ($this->best == 0) {
                break;
            }

            print_r("[GEN #".$i."][MUTATION] happened".PHP_EOL);
            $this->mutation();

            print_r("[GEN #".$i."] Best fitness so far = ".$this->best . PHP_EOL);


            $i++;
        }
        foreach ($this->bestIndividual->kromosoms as $key => $kromosom) {
            print_r("Kromosom #".$key." ".implode('|', $kromosom->gens).PHP_EOL);            
        }
        print_r("[Best] Individu #".$this->bestIndividual->id." fitness = ".$this->bestIndividual->fitness.PHP_EOL);
    }

}

class Timer {
    private $time = null;
    public function __construct() {
        $this->time = time();
        echo 'Working - please wait..<br/>';
    }

    public function __destruct() {
        echo 'Job finished in '.(time()-$this->time).' seconds.';
    }
}

$ga = new Ga();
$t = new Timer();
$ga->run();
unset($t);