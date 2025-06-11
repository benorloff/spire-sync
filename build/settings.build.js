/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/admin/settings/Settings.tsx":
/*!*****************************************!*\
  !*** ./src/admin/settings/Settings.tsx ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__);





const INVENTORY_FILTER_FIELDS = {
  upload: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Upload to WooCommerce', 'spire-sync'),
  status: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Status', 'spire-sync'),
  whse: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Warehouse', 'spire-sync'),
  category: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Category', 'spire-sync'),
  type: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item Type', 'spire-sync'),
  active: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Active Status', 'spire-sync'),
  price: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Price', 'spire-sync'),
  stock: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Stock Level', 'spire-sync'),
  min_stock: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Minimum Stock', 'spire-sync'),
  max_stock: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Maximum Stock', 'spire-sync'),
  taxable: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Taxable', 'spire-sync'),
  weight: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Weight', 'spire-sync'),
  length: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Length', 'spire-sync'),
  width: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Width', 'spire-sync'),
  height: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Height', 'spire-sync')
};
const CONDITION_OPERATORS = {
  equals: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Equals', 'spire-sync'),
  not_equals: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Not Equals', 'spire-sync'),
  contains: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Contains', 'spire-sync'),
  greater_than: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Greater Than', 'spire-sync'),
  less_than: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Less Than', 'spire-sync')
};
const SettingsPage = () => {
  const [settings, setSettings] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
    base_url: "",
    company_name: "",
    api_username: "",
    api_password: "",
    inventory_sync: {
      conditions: [],
      match_type: 'all',
      warehouse_filter: '',
      category_filter: '',
      sync_interval: 'hourly',
      last_sync: '',
      sync_status: 'idle',
      error_message: ''
    }
  });
  const [isLoading, setIsLoading] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [isSaving, setIsSaving] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [message, setMessage] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)("");
  const [error, setError] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)("");
  const [dynamicOptions, setDynamicOptions] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
    warehouses: [],
    categories: [],
    statuses: [],
    types: [],
    lastUpdated: {}
  });
  const [isRefreshing, setIsRefreshing] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const fetchSettings = async () => {
      setIsLoading(true);
      try {
        const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
          path: "/wp/v2/settings"
        });
        const spireSyncSettings = response.spire_sync_settings;
        setSettings(spireSyncSettings);
      } catch (err) {
        setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Failed to load settings", "spire-sync"));
        console.error("Error loading settings:", err);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSettings();
  }, []);
  const handleSave = async () => {
    setIsSaving(true);
    setMessage("");
    setError("");
    try {
      // Encrypt the password first
      const encryptedPassword = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
        path: "/spire-sync/v1/encrypt",
        method: "POST",
        data: {
          data: settings.api_password
        }
      });

      // Create a copy of settings with encrypted password
      const settingsToSave = {
        ...settings,
        api_password: encryptedPassword.data
      };
      const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
        path: "/wp/v2/settings",
        method: "POST",
        data: {
          spire_sync_settings: settingsToSave
        }
      });
      setMessage((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Settings saved successfully", "spire-sync"));
      console.log("Settings saved:", response);
    } catch (err) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Failed to save settings", "spire-sync"));
      console.error("Error saving settings:", err);
    } finally {
      setIsSaving(false);
    }
  };
  const addCondition = () => {
    setSettings(prev => {
      const currentConditions = prev.inventory_sync?.conditions || [];
      return {
        ...prev,
        inventory_sync: {
          ...(prev.inventory_sync || {
            match_type: 'all',
            warehouse_filter: '',
            category_filter: '',
            sync_interval: 'hourly',
            last_sync: '',
            sync_status: 'idle',
            error_message: ''
          }),
          conditions: [...currentConditions, {
            key: '',
            value: '',
            operator: 'equals'
          }]
        }
      };
    });
  };
  const removeCondition = index => {
    setSettings(prev => {
      const currentConditions = prev.inventory_sync?.conditions || [];
      return {
        ...prev,
        inventory_sync: {
          ...(prev.inventory_sync || {
            match_type: 'all',
            warehouse_filter: '',
            category_filter: '',
            sync_interval: 'hourly',
            last_sync: '',
            sync_status: 'idle',
            error_message: ''
          }),
          conditions: currentConditions.filter((_, i) => i !== index)
        }
      };
    });
  };
  const updateCondition = (index, field, value) => {
    setSettings(prev => {
      const currentConditions = prev.inventory_sync?.conditions || [];
      return {
        ...prev,
        inventory_sync: {
          ...(prev.inventory_sync || {
            match_type: 'all',
            warehouse_filter: '',
            category_filter: '',
            sync_interval: 'hourly',
            last_sync: '',
            sync_status: 'idle',
            error_message: ''
          }),
          conditions: currentConditions.map((condition, i) => i === index ? {
            ...condition,
            [field]: value
          } : condition)
        }
      };
    });
  };
  const triggerSync = async () => {
    setIsSaving(true);
    setMessage("");
    setError("");
    try {
      const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
        path: "/spire-sync/v1/inventory/sync",
        method: "POST"
      });
      setMessage((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync started successfully", "spire-sync"));
    } catch (err) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Failed to start sync", "spire-sync"));
      console.error("Error starting sync:", err);
    } finally {
      setIsSaving(false);
    }
  };
  const fetchDynamicOptions = async type => {
    if (!settings.base_url || !settings.api_username || !settings.api_password) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Please configure API settings first", "spire-sync"));
      return;
    }
    setIsRefreshing(type);
    try {
      let endpoint = '';
      switch (type) {
        case 'warehouses':
          endpoint = '/inventory/warehouses';
          break;
        case 'categories':
          endpoint = '/inventory/categories';
          break;
        case 'statuses':
          endpoint = '/inventory/statuses';
          break;
        case 'types':
          endpoint = '/inventory/types';
          break;
      }
      const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_3___default()({
        path: `/spire_sync/v1${endpoint}`,
        method: 'GET'
      });
      if (response.success && Array.isArray(response.data)) {
        setDynamicOptions(prev => ({
          ...prev,
          [type]: response.data,
          lastUpdated: {
            ...prev.lastUpdated,
            [type]: new Date().toISOString()
          }
        }));
      }
    } catch (err) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)(`Failed to fetch ${type}`, "spire-sync"));
      console.error(`Error fetching ${type}:`, err);
    } finally {
      setIsRefreshing(null);
    }
  };
  const getFieldType = field => {
    switch (field) {
      case 'whse':
        return 'warehouses';
      case 'category':
        return 'categories';
      case 'status':
        return 'statuses';
      case 'type':
        return 'types';
      default:
        return null;
    }
  };
  const getOptionsForField = field => {
    switch (field) {
      case 'whse':
        return dynamicOptions.warehouses.map(value => ({
          label: value,
          value
        }));
      case 'category':
        return dynamicOptions.categories.map(value => ({
          label: value,
          value
        }));
      case 'status':
        return dynamicOptions.statuses.map(value => ({
          label: value,
          value
        }));
      case 'type':
        return dynamicOptions.types.map(value => ({
          label: value,
          value
        }));
      default:
        return [];
    }
  };
  const getLastUpdated = field => {
    const type = getFieldType(field);
    if (!type) return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Never', 'spire-sync');
    const date = dynamicOptions.lastUpdated[type];
    if (!date) return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Never', 'spire-sync');
    return new Date(date).toLocaleString();
  };
  if (isLoading) {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Loading...", "spire-sync")
    });
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
    className: "spire-sync-settings-container",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("h1", {
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Spire Sync Settings", "spire-sync")
    }), message && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
      status: "success",
      isDismissible: true,
      onDismiss: () => setMessage(""),
      children: message
    }), error && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
      status: "error",
      isDismissible: true,
      onDismiss: () => setError(""),
      children: error
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Panel, {
      header: "Settings",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)((react__WEBPACK_IMPORTED_MODULE_0___default().Fragment), {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("API Settings", "spire-sync"),
          initialOpen: true,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelRow, {
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Base URL", "spire-sync"),
              value: settings.base_url,
              onChange: value => setSettings({
                ...settings,
                base_url: value || ""
              }),
              help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Enter the base URL for the Spire API (e.g., http://example.com/api/v2)", "spire-sync"),
              style: {
                width: "400px"
              }
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelRow, {
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Company Name", "spire-sync"),
              value: settings.company_name,
              onChange: value => setSettings({
                ...settings,
                company_name: value || ""
              }),
              style: {
                width: "400px"
              }
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelRow, {
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("API Username", "spire-sync"),
              value: settings.api_username,
              onChange: value => setSettings({
                ...settings,
                api_username: value || ""
              }),
              style: {
                width: "400px"
              }
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelRow, {
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("API Password", "spire-sync"),
              type: "password",
              value: settings.api_password,
              onChange: value => setSettings({
                ...settings,
                api_password: value || ""
              }),
              style: {
                width: "400px"
              }
            })
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Inventory Sync Settings", "spire-sync"),
          initialOpen: true,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
            className: "spire-sync-conditions",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("h3", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync Conditions", "spire-sync")
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.RadioControl, {
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Match Type", "spire-sync"),
              selected: settings.inventory_sync?.match_type || 'all',
              options: [{
                label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('All conditions must be met', 'spire-sync'),
                value: 'all'
              }, {
                label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Any condition must be met', 'spire-sync'),
                value: 'any'
              }],
              onChange: value => setSettings(prev => ({
                ...prev,
                inventory_sync: {
                  ...(prev.inventory_sync || {
                    conditions: [],
                    match_type: 'all',
                    warehouse_filter: '',
                    category_filter: '',
                    sync_interval: 'hourly',
                    last_sync: '',
                    sync_status: 'idle',
                    error_message: ''
                  }),
                  match_type: value
                }
              }))
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
              className: "spire-sync-conditions-list",
              children: (settings.inventory_sync?.conditions || []).map((condition, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
                className: "spire-sync-condition",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
                  className: "spire-sync-condition-fields",
                  children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
                    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Field", "spire-sync"),
                    value: condition.key,
                    options: [{
                      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select a field', 'spire-sync'),
                      value: ''
                    }, ...Object.entries(INVENTORY_FILTER_FIELDS).map(([key, label]) => ({
                      label,
                      value: key
                    }))],
                    onChange: value => updateCondition(index, 'key', value)
                  }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
                    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Operator", "spire-sync"),
                    value: condition.operator,
                    options: [{
                      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select an operator', 'spire-sync'),
                      value: ''
                    }, ...Object.entries(CONDITION_OPERATORS).map(([key, label]) => ({
                      label,
                      value: key
                    }))],
                    onChange: value => updateCondition(index, 'operator', value)
                  }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
                    className: "spire-sync-condition-value",
                    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
                      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Value", "spire-sync"),
                      value: condition.value,
                      options: [{
                        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select a value', 'spire-sync'),
                        value: ''
                      }, ...getOptionsForField(condition.key)],
                      onChange: value => updateCondition(index, 'value', value)
                    }), ['whse', 'category', 'status', 'type'].includes(condition.key) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
                      className: "spire-sync-condition-value-actions",
                      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                        isSmall: true,
                        onClick: () => {
                          const type = getFieldType(condition.key);
                          if (type) {
                            fetchDynamicOptions(type);
                          }
                        },
                        disabled: isRefreshing === getFieldType(condition.key),
                        children: isRefreshing === getFieldType(condition.key) ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {}) : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Refresh', 'spire-sync')
                      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("span", {
                        className: "spire-sync-last-updated",
                        children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Last updated:', 'spire-sync'), " ", getLastUpdated(condition.key)]
                      })]
                    })]
                  }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                    isDestructive: true,
                    onClick: () => removeCondition(index),
                    className: "spire-sync-remove-condition",
                    children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Remove", "spire-sync")
                  })]
                })
              }, index))
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
              isPrimary: true,
              onClick: addCondition,
              className: "spire-sync-add-condition",
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Add Condition", "spire-sync")
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
            className: "spire-sync-filters",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("h3", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Additional Filters", "spire-sync")
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Warehouse Filter", "spire-sync"),
              value: settings.inventory_sync?.warehouse_filter || '',
              onChange: value => setSettings(prev => ({
                ...prev,
                inventory_sync: {
                  ...prev.inventory_sync,
                  warehouse_filter: value || ''
                }
              })),
              help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Optional: Filter by specific warehouse", "spire-sync")
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalInputControl, {
              __next40pxDefaultSize: true,
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Category Filter", "spire-sync"),
              value: settings.inventory_sync?.category_filter || '',
              onChange: value => setSettings(prev => ({
                ...prev,
                inventory_sync: {
                  ...prev.inventory_sync,
                  category_filter: value || ''
                }
              })),
              help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Optional: Filter by specific category", "spire-sync")
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
            className: "spire-sync-schedule",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("h3", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync Schedule", "spire-sync")
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync Interval", "spire-sync"),
              value: settings.inventory_sync?.sync_interval || 'hourly',
              options: [{
                label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hourly', 'spire-sync'),
                value: 'hourly'
              }, {
                label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Daily', 'spire-sync'),
                value: 'daily'
              }, {
                label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Weekly', 'spire-sync'),
                value: 'weekly'
              }],
              onChange: value => setSettings(prev => ({
                ...prev,
                inventory_sync: {
                  ...prev.inventory_sync,
                  sync_interval: value
                }
              }))
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
            className: "spire-sync-status",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("h3", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync Status", "spire-sync")
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("p", {
              children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Last Sync:", "spire-sync"), " ", settings.inventory_sync?.last_sync || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Never', 'spire-sync')]
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("p", {
              children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Status:", "spire-sync"), " ", settings.inventory_sync?.sync_status || 'idle']
            }), settings.inventory_sync?.error_message && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Notice, {
              status: "error",
              isDismissible: false,
              children: settings.inventory_sync.error_message
            })]
          })]
        })]
      }, "0")
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
      className: "spire-sync-actions",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
        variant: "primary",
        onClick: handleSave,
        disabled: isSaving,
        children: isSaving ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Saving...", "spire-sync") : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Save Settings", "spire-sync")
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
        variant: "secondary",
        onClick: triggerSync,
        disabled: isSaving,
        children: isSaving ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Starting Sync...", "spire-sync") : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Sync Now", "spire-sync")
      })]
    })]
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SettingsPage);

/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**************************************!*\
  !*** ./src/admin/settings/index.tsx ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Settings__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Settings */ "./src/admin/settings/Settings.tsx");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('spire-sync-settings-root');
  if (container) {
    const root = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createRoot)(container);
    root.render(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_Settings__WEBPACK_IMPORTED_MODULE_2__["default"], {}));
  }
});
})();

/******/ })()
;
//# sourceMappingURL=settings.build.js.map