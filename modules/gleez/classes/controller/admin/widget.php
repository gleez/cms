<?php
/**
 * Admin Widget Controller
 *
 * @package    Gleez\Controller\Admin
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Controller_Admin_Widget extends Controller_Admin {

	/**
	 * Denotes that a widget is not enabled in any region and should not be shown.
	 */
	protected static $WIDGET_REGION_NONE = -1;

	/**
	 * Listing Widgets
	 */
	public function action_index()
	{
		$this->title = __('Widgets');

		$view = View::factory('admin/widget/list')
					->bind('widget_regions', $widget_regions)
					->bind('weight_delta',   $weight_delta)
					->bind('widgets',        $widget_listing);

		$widget_regions = array();
		$theme_name = Kohana::$config->load('site.theme', Gleez::$theme);
		$theme = Theme::get_info($theme_name);

		if(isset($theme->regions) AND ! empty($theme->regions))
		{
			$widget_regions = $theme->regions;
		}

		// Add a last region for disabled blocks.
		$widget_regions = Arr::merge($widget_regions, array(self::$WIDGET_REGION_NONE => self::$WIDGET_REGION_NONE));

		//$current_widgets = Kohana::list_files('classes/widget');

		$widgets = ORM::factory('widget')
						->order_by('region')
						->order_by('weight')
						->find_all();

		// Weights range from -delta to +delta, so delta should be at least half
		// of the amount of blocks present. This makes sure all blocks in the same
		// region get an unique weight.
		$weight_delta = round(count($widgets) / 2);

		if (isset($widget_regions[self::$WIDGET_REGION_NONE]))
		{
			$widget_regions[self::$WIDGET_REGION_NONE] = __('Disabled');
		}

		foreach ($widget_regions as $key => $value)
		{
			// Initialize an empty array for the region.
			$widget_listing[$key] = array();
		}

		// Initialize disabled widgets array.
		$widget_listing[self::$WIDGET_REGION_NONE] = array();

		// Add each block in the form to the appropriate place in the widget listing.
		foreach ($widgets as $widget)
		{
			// Fetch the region for the current widget.
			$region = (isset($widget->region) ? $widget->region : self::$WIDGET_REGION_NONE);
			$widget_listing[$region][] = $widget;
		}

		Assets::js('widgets', 'media/js/widgets.js', array('jquery'), FALSE, array('weight' => 5));

		foreach ($widget_regions as $region => $title)
		{
			Assets::tabledrag('widgets','match','sibling','widget-region-select','widget-region-'.$region,NULL,FALSE);
			Assets::tabledrag('widgets', 'order', 'sibling', 'widget-weight', 'widget-weight-' . $region);
		}

		if ($this->valid_post('widget-list'))
		{
			foreach ($_POST['widgets'] as $widget)
			{
				$widget['status'] = (int) ($widget['region'] != self::$WIDGET_REGION_NONE);
				$widget['region'] = $widget['status'] ? $widget['region'] : self::$WIDGET_REGION_NONE;

				DB::update('widgets')
					->set(array(
						'status'=> $widget['status'],
						'weight' => $widget['weight'],
						'region' => $widget['region'])
					)
					->where('id','=',$widget['id'])
					->execute();
			}

			Message::success(__('The Widget settings have been updated.'));
			Cache::instance('widgets')->delete_all();

			$this->request->redirect(Route::get('admin/widget')->uri());
		}

		$this->response->body($view);
	}

	/**
	 * Adding Widgets
	 */
	public function action_add()
	{
		$widget = ORM::factory('widget');

		$widget_regions = array();
		$theme_name = Kohana::$config->load('site.theme', Gleez::$theme);
		$theme = Theme::get_info($theme_name);

		if(isset($theme->regions) AND ! empty($theme->regions))
		{
			$widget_regions = $theme->regions;
		}
		// Add a last region for disabled blocks.
		$widget_regions = Arr::merge($widget_regions, array(self::$WIDGET_REGION_NONE => self::$WIDGET_REGION_NONE));

		if (isset($widget_regions[self::$WIDGET_REGION_NONE]))
		{
			$widget_regions[self::$WIDGET_REGION_NONE] = __('Disabled');
		}

		$all_roles = ORM::factory('role')->find_all()->as_array('id', 'name');

		$this->title = __('Add widget');
		$view = View::factory('admin/widget/form')
					->set('widget', $widget)
					->set('fields', '')
					->set('roles', $all_roles)
					->set('regions', $widget_regions);

		if ($this->valid_post('widget'))
		{
			$widget->values($_POST);
			try
			{
				$widget->name = 'static/'. Text::random('alnum', 6);
				$widget->module = 'gleez';
				$widget->save();

				Message::success(__('Widget %name created successful!', array('%name' => $widget->title)));
				Cache::instance('widgets')->delete_all();

				// Redirect to listing
				$this->request->redirect(Route::get('admin/widget')->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors('models');
			}
		}

                Assets::select2();
		$this->response->body($view);
	}

	/**
	 * Editing Widgets
	 */
	public function action_edit()
	{
		$id = (int) $this->request->param('id', 0);
		$widget = ORM::factory('widget', $id);

		if ( ! $widget->loaded())
		{
			Log::error('Attempt to access non-existent widget.');
			Message::error(__('Widget doesn\'t exists!'));

			$this->request->redirect(Route::get('admin/widget')->uri());
		}

		$widget_regions = array();
		$theme_name = Kohana::$config->load('site.theme', Gleez::$theme);
		$theme = Theme::get_info($theme_name);

		$handler = Widget::factory($widget->name, $widget);
		$fields = $handler->form();

		if(isset($theme->regions) AND ! empty($theme->regions))
		{
			$widget_regions = $theme->regions;
		}

		// Add a last region for disabled blocks.
		$widget_regions = Arr::merge($widget_regions, array(self::$WIDGET_REGION_NONE => self::$WIDGET_REGION_NONE));

		if (isset($widget_regions[self::$WIDGET_REGION_NONE]))
		{
			$widget_regions[self::$WIDGET_REGION_NONE] = __('Disabled');
		}

		$all_roles = ORM::factory('role')
						->find_all()
						->as_array('id', 'name');

		$this->title = __('Edit %widget widget', array('%widget' => $widget->title));

		$view = View::factory('admin/widget/form')
					->set('widget', $widget)
					->set('fields', $fields)
					->set('roles',  $all_roles)
					->set('regions', $widget_regions);

		if ($this->valid_post('widget'))
		{
			$widget->values($_POST);
			try
			{
				$widget->save();
				if(isset($_POST['widget']))
				{
					unset($_POST['widget'], $_POST['_token'], $_POST['_action']);
				}

				$handler->save($_POST);
				Message::success(__('Widget %name updated successful!', array('%name' => $widget->title)));
				Cache::instance('widgets')->delete_all();

				// Redirect to listing
				$this->request->redirect(Route::get('admin/widget')->uri());
			}
			catch (ORM_Validation_Exception $e)
			{
				$view->errors = $e->errors('models');
			}
		}

                Assets::select2();
		$this->response->body($view);
	}

	/**
	 * Deleting Widgets
	 */
	public function action_delete()
	{
		$id = (int) $this->request->param('id', 0);
		$widget = ORM::factory('widget', $id);

		if ( ! $widget->loaded())
		{
			Log::error('Attempt to access non-existent widget.');
			Message::error(__('Widget doesn\'t exists!'));

			$this->request->redirect(Route::get('admin/widget')->uri());
		}

		$split_name = explode('/', $widget->name);
		$static = ($split_name AND $split_name[0] == 'static') ? TRUE : FALSE;

		// we can only delete if its a custom widget
		if( ! $static)
		{
			$this->request->redirect(Route::get('admin/widget')->uri());
		}

		$handler = Widget::factory($widget->name, $widget);
		$this->title = __('Delete :title', array(':title' => $widget->title ));
		$destination = ($this->request->query('destination') !== NULL) ?
			array('destination' => $this->request->query('destination')) : array();

		$view = View::factory('form/confirm')
					->set('action', Route::get('admin/widget')
					->uri( array('action' => 'delete', 'id' => $widget->id) ).URL::query($destination) )
					->set('title', $widget->title);

		// If deletion is not desired, redirect to post
		if (isset($_POST['no']) AND $this->valid_post())
		{
			$this->request->redirect(Route::get('admin/widget')->uri(array('id' => $widget->id)));
		}

		// If deletion is confirmed
		if (isset($_POST['yes']) AND $this->valid_post())
		{
			try
			{
				$title = $widget->title;
				$widget->delete();
				$handler->delete($_POST);

				Message::success(__('Widget :title deleted successful!', array(':title' => $title)));
				Cache::instance('widgets')->delete_all();
			}
			catch (Exception $e)
			{
				Log::error('Error occurred deleting widget id: :id, :msg',
					array(':id' => $widget->id, ':msg' => $e->getMessage())
				);
				Message::error(__('An error occurred deleting widget %title', array(':title' => $widget->title)));
			}

			$redirect = empty($destination) ? Route::get('admin/widget')->uri() :
			$this->request->query('destination');

			$this->request->redirect($redirect);
		}

		$this->response->body($view);
	}

}
