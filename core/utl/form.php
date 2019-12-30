<?php
/**
 * Description of form
 *
 ** @author Valentin Balt <valentin.balt@gmail.com>
 */
class Form
{
	const ELEMENTS = 'elements';
	const NAME = 'name';
	const LABEL = 'label';
	const TYPE = 'type';
	
	static public function value($value)
	{
		return htmlspecialchars($value);
	}
	
	static public function render($form, $output=false, $values=null)
	{
		try {
			
		$pfbc = new PFBC\Form(
				isset($form['form']['name'])
					? $form['form']['name']
					: 'form'
				);
		
		if (isset($form['form']['enctype'])) {
			$pfbc->configure(array ('enctype' => $form['form']['enctype']));
		}
		
		if (isset($form['form']['view'])) {
			$view = "PFBC\\View\\".$form['form']['view'];
		} else {
			$view = "PFBC\\View\\SideBySide";
		}
			
		
		$pfbc->configure(array(
			"action" => isset($form['form']['action'])
							? $form['form']['action']
							: \Url::getPath(),
			"prevent" => array("bootstrap", "jquery", "jqueryui"),
			'jQueryOptions' => array (
				'changeMonth' => true,
				'changeYear' => true,
			),
			"view" => new $view,
		));
		
		if (isset ($form['form']['legend'])) {
			$pfbc->addElement(new PFBC\Element\HTML('<legend>' . $form['form']['legend'] . '</legend>'));
		}
		if (isset ($form['hidden']) && is_array ($form['hidden'])) {
			foreach ($form['hidden'] as $el) {
				$pfbc->addElement(new PFBC\Element\Hidden($el['name'], $el['value']));
			}
		}
		foreach ($form['elements'] as $el) {
			if (isset ($el['name']) && isset ($values[$el['name']])) {
				$el['value'] = $values[$el['name']];
			}
			$Element = 'PFBC\\Element\\' . $el['type'];
		
			$objects2ajust = array ('select', 'textbox', 'textarea', 'number', 'password', 'phone', 'email');
			foreach ($objects2ajust as $on) {
				if (stristr($Element, $on) !== false) {
					
					if (!isset ($el['properties']['class'])) {
						$el['properties']['class'] = 'form-control';
					} else {
						if (!preg_match ('/form-control/', $el['properties']['class'])) {
							$el['properties']['class'] .= ' form-control';
						}
					}
					break;
				}
			}
		
			if (!isset($el['properties'])) {
				$el['properties']['required'] = 1;
			}
			if (!isset($form['form']['labelWidth'])) {
				$el['properties']['labelWidth'] = 2;
			} elseif ($form['form']['labelWidth']) {
				$el['properties']['labelWidth'] = $form['form']['labelWidth'];
			}
			if (!isset($form['form']['controlWidth'])) {
				$el['properties']['controlWidth'] = 6;
			} elseif ($form['form']['controlWidth']) {
				$el['properties']['controlWidth'] = $form['form']['controlWidth'];
			}
			if (!isset($el['properties']['elementWidth']) && !empty($form['form']['elementWidth'])) {
				$el['properties']['elementWidth'] = $form['form']['elementWidth'];
			}

			if (isset($el['properties']['required']) && !$el['properties']['required']) {
				unset ($el['properties']['required']);
			}
			if (isset($el['properties']['readonly']) && $el['properties']['readonly']) {
				$el['properties']['readonly'] = 'readonly';
			}
			if ($el['type'] == 'HTML') {
				$p1 = $el['value'];
				$p2 = $p3 = $p4 = null;
			} elseif (in_array ($el['type'], array (
				'Select', 'Radio', 'Checkbox', 'Checksort', 'Sort'
			))) {
				if (isset($el['value'])) {
					$el['properties']['value'] = $el['value'];
				}
				$p1 = isset($el['label']) ? $el['label'] : '';
				$p2 = isset($el['name']) ? $el['name'] : '';
				$p3 = $el['options'];
				$p4 = isset($el['properties']) ? $el['properties'] : null;
			} elseif (in_array ($el['type'], array (
				'Hidden'
			))) {
				$p1 = isset($el['name']) ? $el['name'] : '';
				$p2 = isset($el['value']) ? $el['value'] : '';
				$p3 = isset($el['properties']) ? $el['properties'] : null;
				$p4 = null;
			} else {
				$p1 = isset($el['label']) ? $el['label'] : '';
				$p2 = isset($el['name']) ? $el['name'] : '';
				$p3 = array_merge(
							array ('value' => isset($el['value'])?$el['value']:null),
							isset($el['properties']) ? $el['properties'] : null
						);
				$p4 = null;
			}
			$pfbc->addElement(
					new $Element (
						$p1,
						$p2,
						$p3,
						$p4
					)
			);
		}
		return $pfbc->render($output);

		} catch (Exception $e) {
			
		}
	}
	
}

