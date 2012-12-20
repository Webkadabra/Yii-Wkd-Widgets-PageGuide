=== Page Guides ===

Documentaiton is not ready yet, but code is pretty easy and here i an example of how to use this widget (given that you have copied extension itself to your application first):

=== Usage: ===

1. Add this action to Sitecontroller (or any other controller):

~~~
public function actionPushUserState() {
	if(!app()->request->isAjaxRequest) {
		throw new CHttpException(403);
	}
	if(isset($_POST['key'])) {
		app()->user->setState($_POST['key'], true);
		echo 'ok';
	}
}
~~~

2. Setup totirials (in view files), like this:
~~~
<?php
	// ...
	// In view file:
	// Create page guide
	$this->widget('extensions.widgets.WkdPageGuide',array(
		'key'=>'users.informAboutSomeElement',
		'autoStart'=>true, // Start tutorial when page loads
		'stateful'=>true, // Show only once per user session
		'steps'=>array(
			array(
				'id'=>'hello', // required
				'position'=>12,
				'next'=>'helpLink', // required if you need next step
				'title'=>'New This Are Here!',
				'description'=>'<p>Dear '.app()->user->name.',<br>we have added some cool important features!</p>',
				'buttons'=>array(
					'{next}',
				),
				'overlay'=>true,
			),
			array(
				'id'=>'helpLink',
				'attachTo'=>'a.pageHelpLink:last',
				'title'=>'Support Section is available',
				'description'=>'<p>This is a link to get help.</p>',
				'buttons'=>array(
					'{complete}',
				),
				'position'=>11,
				'overlay'=>false,
			),
		),
	));
	// ...
~~~

You can configure any buttons, but we have few "templates", to save time:
{next} - Go to next step
{close} - close tutorial
{complete} - close tutorial and mark it as "completed" (for stateful tutorials)

Please, see documentaiton in code for details. 

=== Todo ===
Add more support for stateful guides (permanent etc.)