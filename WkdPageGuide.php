<?php
/**
 * WkdPageGuide class file.
 *
 * @author Sergii Gamaiunov <hello@webkadabra.com>
 */

/**
 * WkdPageGuide
 * @todo DOCUMENTATION
 * WkdPageGuide encapsulates the {@link http://jeffpickhardt.com/guiders/ guiders} plugin.
 */
class WkdPageGuide extends CWidget
{
	/**
	 * @var array Steps of tour
	 */
	public $steps=array();
	/**
	 * @var Tour key, by which we'd identify it in database
	 */
	public $key;
	public $autoStart=false;
	public $stateful=false;
	public $assetsUrl=null;
	/**
	 * @var array The default options called just one time per request. This options will alter every other CJuiDatePicker instance in the page.
	 * It has to be set at the first call of CJuiDatePicker widget in the request.
	 */
	public $defaultOptions;

	public $pushStateUrl=null;

	
	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{
		if($this->stateful) {
			// Did user already saw it?
			if(app()->user->hasState('WkdPageGuide.'.$this->key)) {
				return;
			}
		}

		if($this->pushStateUrl===null) {
			$this->pushStateUrl= Yii::app()->createUrl('/site/pushUserState');
		}
		$cs=Yii::app()->getClientScript();
		if($this->assetsUrl===null){
			$path =  __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'wkdPageGuide' . DIRECTORY_SEPARATOR . 'guiders';
			$this->assetsUrl=CHtml::asset($path);
		}


		$cs->registerScriptFile($this->assetsUrl.'/guiders-1.3.0.js');
		$cs->registerCssFile($this->assetsUrl.'/guiders-1.3.0.css');

		$id = $this->id;

		$this->normalizeSteps();


		if($this->steps) {
			$output='';
			$started=false;
			foreach($this->_steps as $step) {

				$output .= 'guiders.createGuider('.CJavaScript::encode($step).')';
				if($this->autoStart && !$started) {
					if($this->autoStart===true) {
						// Simply start from first frame
						$output .= '.show()';
						$started=true;
					} else if($step['id'] == $this->autoStart) {
						$output .= '.show()';
						$started=true;
					}

				}

				$output .= ';';
			}

			$cs->registerScript(__CLASS__.'#'.$id,$output);

		}

		#$cs->registerScript(__CLASS__, 	$this->defaultOptions?'jQuery.datetimepicker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':'');
		#$cs->registerScript(__CLASS__.'#'.$id, $js);

	}

	protected $_steps=array();
	protected function normalizeSteps() {
		if(!$this->steps)
			return;

		$i = 1;
		foreach($this->steps as $key => $config) {
			$push=array();
			$buttons=array();
			if(isset($config['buttons']) && !empty($config['buttons'])) {
				foreach($config['buttons'] as $key2 => $btnOptions) {

					if(!is_array($btnOptions) && !empty($btnOptions)) {

						// We have templates fof buttons: {next}, {close} & {complete}
						if($btnOptions==='{next}') {
							$btnOptions=array(
								'name'=>t('Next'),
								'onclick'=>'js:guiders.next',
							);
						} elseif($btnOptions==='{close}') {
							$btnOptions=array(
								'name'=>t('Close'),
								'onclick'=>'js:guiders.hideAll',
							);
						} elseif($btnOptions==='{complete}') { // Same as close, but send AJAX request to remember completion of tour

							$data = array('key'=>'WkdPageGuide.'.$this->key);
							if(app()->request->enableCsrfValidation) {
								$data['fingerprint'] = app()->request->csrfToken;
							}
							$btnOptions=array(
								'name'=>t('Close'),
								'onclick'=>'js:function(){
									guiders.hideAll();
									$.post("'.$this->pushStateUrl.'",'.CJavaScript::encode($data).');
								}',
							);
						}
						$buttons[]=$btnOptions;
					} else {

					}
				}
			}

			$config['buttons'] = $buttons;

			if(!isset($config['id'])) {
				$config['id'] = $this->id.'.step'.$i;

			}
			$this->_steps[] = $config;
			$i++;
		}
	}
}