<div class="crm-container">
  <div crm-ui-debug="$ctrl.myContact"></div>

  <h1 crm-page-title>{{ts('About %1', {1: $ctrl.myContact.first_name + ' ' + $ctrl.myContact.last_name})}}</h1>

  <form name="myForm" crm-ui-id-scope>

    <div class="help">
      <p>{{ts('This is an auto-generated skeletal page.')}}</p>

      <p>{{ts('To view more debugging information, edit the URL and include "?angularDebug=1".')}}</p>
    </div>

    <div crm-ui-accordion="{title: ts('Greeting')}">
      <p ng-show="$ctrl.myContact.first_name">{{ ts('Hello, %1.', {1: $ctrl.myContact.first_name}) }}</p>

      <p ng-show="!$ctrl.myContact.first_name">{{ ts('Hello, stranger.') }}</p>
    </div>

    <div crm-ui-accordion="{title: ts('About Me')}">
      <div class="crm-block">
        <div class="crm-group">
          <div crm-ui-field="{name: 'myForm.first_name', title: ts('Name'), help: hs('full_name')}">
            <input
              crm-ui-id="myForm.first_name"
              name="first_name"
              ng-model="$ctrl.myContact.first_name"
              class="crm-form-text"
              placeholder="{{ts('First')}}"
              />
            <input
              crm-ui-id="myForm.last_name"
              name="last_name"
              ng-model="$ctrl.myContact.last_name"
              class="crm-form-text"
              placeholder="{{ts('Last')}}"
              />
          </div>
        </div>
      </div>

      <div>
        <button ng-click="$ctrl.save()">{{ts('Save')}}</button>
      </div>
    </div>

  </form>

</div>
