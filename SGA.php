<?php

class Chromosome {
    public static  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-+,. ';
    public $genes = [];
    public $fitness = 0;

    public function __construct($genes = [])
    {
        $this->genes = $genes;
    }

    public function generateGane($size)
    {   
        for ($i=0; $i < $size; $i++) { 
            $this->genes[$i] = Chromosome::$characters[rand(0, strlen(Chromosome::$characters) - 1)];
        }
    }

}

class Population {
 
    public $size;
    public $chromosomes = [];

    public function __construct($size)
    {
       $this->size = $size; 
    }

    public function initialize($goal)
    {
        for ($i=0; $i < $this->size + 1; $i++) { 
            $chromosome = new Chromosome();
            $chromosome->generateGane(strlen($goal));
            $this->chromosomes[] = $chromosome;
        }
    }

}



class GA {

    protected $goal;
    public $population, $best;

    protected $config = [
        'population_size' => 10,
        'max_iteration' => 50000,
		'delay' => 500, // ms, if debug is false, then delay forced to 0
		'debug' => true,
		'fitness_in_percent' => false, // usefull if chromosome more than 10 chars
        'selection' => 90, // percent
		'mutation' => 1, // percent
    ];
    
    public function __construct($goal)
    {
        $this->goal = $goal;
        $this->population = new Population($this->config['population_size']);
        $this->population->initialize($this->goal);
    }

    public function fitness()
    {
        $chromosomes = $this->population->chromosomes;
        $goal = str_split($this->goal);

        for ($i=0; $i < count($chromosomes) - 1; $i++) { 
            $chromosome = $chromosomes[$i];
            for ($j=0; $j < count($chromosome->genes); $j++) { 
                $gen = $chromosome->genes[$j];
                if($gen == $goal[$j]){
                    $chromosome->fitness++;
                }
                // $chromosome->fitness = $chromosome->fitness / count($goal) * 100;
            }

        }   
    }

    public function selection()
    {
        $max_selection = floor($this->config['selection'] / 100 * $this->config['population_size']);
        $tmp_array = [];
        foreach ($this->population->chromosomes as $key => $chromosome) {
            $tmp_array[$key] = $chromosome->fitness;
        }
        arsort($tmp_array);
        $tmp_array = array_slice($tmp_array, 0, $max_selection, true);
        // print_r('max_selection ' . implode(',',array_keys(array_slice($tmp_array, 0, $max_selection, true))) . PHP_EOL);
		$tmp_array = array_keys($tmp_array);

        $tmp_population = $this->population->chromosomes;
        foreach ( $tmp_population as $key => $chromosome) {
            // print_r('stay' . implode(',',$tmp_array) . PHP_EOL);
			if(!in_array($key, $tmp_array)){
                print_r("unset " . $key . PHP_EOL);
                // unset
                unset($tmp_population[$key]);
			}
        }
        // print('after unset count ' . count($tmp_population) . PHP_EOL);
        $this->population->chromosomes = $tmp_population;
    }

    public function crossOver()
    {
        $new_population = array();
		for($i = 0; $i < $this->config['population_size']; $i++){
			// get random parents
			$a = $this->population->chromosomes[array_rand($this->population->chromosomes, 1)];
			$b = $this->population->chromosomes[array_rand($this->population->chromosomes, 1)];
			
			$a = $a->genes;
			$b = $b->genes;
			
            // print(implode($a) . " x " .implode($b) . PHP_EOL);
			// get random chromosome from parents
			$child = '';
			for($j = 0; $j < count($a); $j++){
				$child .= rand(0, 1) == 0 ? $a[$j] : $b[$j];
			}
			$new_population[] = new Chromosome(str_split($child));
			// $new_population[] = array(
			// 	'chromosome' => $child,
			// 	'fitness' => 0,
			// );
		}
		
		$this->population->chromosomes = $new_population;
    }

    public function mutation(){
		foreach($this->population->chromosomes as $k => $v){
			// get mutation chance
			$is_mutating = (rand(0, 1000) / 1000 * 100) <= $this->config['mutation'];
			if(!$is_mutating) continue;
			
			$tmp = $v->genes;
			$key = array_rand($tmp);
			
			$tmp[$key] = str_split($this->goal)[$key];
			$this->population->chromosomes[$k]->genes = $tmp;
		}
	}

	public function getBest(){
		$this->fitness();
		$best_i = 0;
		$tmp = 0;
		foreach($this->population->chromosomes as $k => $v){
			if($v->fitness > $tmp){
				$tmp = $v->fitness;
				$best_i = $k;
			}
		}
		
		return $this->best = implode('', $this->population->chromosomes[$best_i]->genes);
	}

    public function run()
    {
        $i = 0;
        while ($i < $this->config['max_iteration'] && $this->best != $this->goal) {
            $this->best = $this->getBest();
            echo 'Generation #' . $i . ' - ' . $this->best . PHP_EOL;
            foreach ($this->population->chromosomes as $key => $c) {
                print_r('Generation #'. $i . ' ' . implode('',$c->genes) . ' fitness = '. $c->fitness . PHP_EOL);
            }

            $this->fitness();
            $this->selection();
            $this->crossOver();
            $this->mutation();
            // usleep($this->config['delay'] * 1000);
            $i++;
        }
        
        print_r('Solution found - ' . $this->best . PHP_EOL);
    }


}

$k = new GA('SMK N 1 BANTUL');
$best = $k->run();
