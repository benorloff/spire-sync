import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import {
  Panel,
  PanelBody,
  PanelRow,
  __experimentalInputControl as InputControl,
  Button,
  Notice,
  SelectControl,
  RadioControl,
  Card,
  CardHeader,
  CardBody,
  Spinner,
} from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";

interface Condition {
  key: string;
  value: string;
  operator: string;
}

interface InventorySyncSettings {
  conditions: Condition[];
  match_type: 'all' | 'any';
  warehouse_filter: string;
  category_filter: string;
  sync_interval: 'hourly' | 'daily' | 'weekly';
  last_sync: string;
  sync_status: 'idle' | 'running' | 'error';
  error_message: string;
}

interface Settings {
  base_url: string;
  company_name: string;
  api_username: string;
  api_password: string;
  inventory_sync?: InventorySyncSettings;
}

interface SettingsResponse {
  spire_sync_settings: Settings;
}

interface EncryptionResponse {
  success: boolean;
  data: string;
}

interface DynamicOptions {
  warehouses: string[];
  categories: string[];
  statuses: string[];
  types: string[];
  lastUpdated: {
    warehouses?: string;
    categories?: string;
    statuses?: string;
    types?: string;
  };
}

interface ApiResponse {
  success: boolean;
  data: any;
}

const INVENTORY_FILTER_FIELDS = {
  upload: __('Upload to WooCommerce', 'spire-sync'),
  status: __('Status', 'spire-sync'),
  whse: __('Warehouse', 'spire-sync'),
  category: __('Category', 'spire-sync'),
  type: __('Item Type', 'spire-sync'),
  active: __('Active Status', 'spire-sync'),
  price: __('Price', 'spire-sync'),
  stock: __('Stock Level', 'spire-sync'),
  min_stock: __('Minimum Stock', 'spire-sync'),
  max_stock: __('Maximum Stock', 'spire-sync'),
  taxable: __('Taxable', 'spire-sync'),
  weight: __('Weight', 'spire-sync'),
  length: __('Length', 'spire-sync'),
  width: __('Width', 'spire-sync'),
  height: __('Height', 'spire-sync')
};

const CONDITION_OPERATORS = {
  equals: __('Equals', 'spire-sync'),
  not_equals: __('Not Equals', 'spire-sync'),
  contains: __('Contains', 'spire-sync'),
  greater_than: __('Greater Than', 'spire-sync'),
  less_than: __('Less Than', 'spire-sync')
};

const SettingsPage: React.FC = () => {
  const [settings, setSettings] = useState<Settings>({
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
  const [isLoading, setIsLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [dynamicOptions, setDynamicOptions] = useState<DynamicOptions>({
    warehouses: [],
    categories: [],
    statuses: [],
    types: [],
    lastUpdated: {}
  });
  const [isRefreshing, setIsRefreshing] = useState<string | null>(null);

  useEffect(() => {
    const fetchSettings = async () => {
      setIsLoading(true);
      try {
        const response = await apiFetch({
          path: "/wp/v2/settings",
        }) as SettingsResponse;
        
        const spireSyncSettings = response.spire_sync_settings;
        setSettings(spireSyncSettings);
      } catch (err) {
        setError(__("Failed to load settings", "spire-sync"));
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
      const encryptedPassword = await apiFetch({
        path: "/spire-sync/v1/encrypt",
        method: "POST",
        data: { data: settings.api_password }
      }) as EncryptionResponse;

      // Create a copy of settings with encrypted password
      const settingsToSave = {
        ...settings,
        api_password: encryptedPassword.data
      };

      const response = await apiFetch({
        path: "/wp/v2/settings",
        method: "POST",
        data: {
          spire_sync_settings: settingsToSave,
        },
      });

      setMessage(__("Settings saved successfully", "spire-sync"));
      console.log("Settings saved:", response);
    } catch (err) {
      setError(__("Failed to save settings", "spire-sync"));
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
          conditions: [
            ...currentConditions,
            { key: '', value: '', operator: 'equals' }
          ]
        }
      };
    });
  };

  const removeCondition = (index: number) => {
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

  const updateCondition = (index: number, field: keyof Condition, value: string) => {
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
          conditions: currentConditions.map((condition, i) => 
            i === index ? { ...condition, [field]: value } : condition
          )
        }
      };
    });
  };

  const triggerSync = async () => {
    setIsSaving(true);
    setMessage("");
    setError("");

    try {
      const response = await apiFetch({
        path: "/spire-sync/v1/inventory/sync",
        method: "POST"
      });

      setMessage(__("Sync started successfully", "spire-sync"));
    } catch (err) {
      setError(__("Failed to start sync", "spire-sync"));
      console.error("Error starting sync:", err);
    } finally {
      setIsSaving(false);
    }
  };

  const fetchDynamicOptions = async (type: keyof Omit<DynamicOptions, 'lastUpdated'>) => {
    if (!settings.base_url || !settings.api_username || !settings.api_password) {
      setError(__("Please configure API settings first", "spire-sync"));
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

      const response = await apiFetch({
        path: `/spire_sync/v1${endpoint}`,
        method: 'GET'
      }) as ApiResponse;

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
      setError(__(`Failed to fetch ${type}`, "spire-sync"));
      console.error(`Error fetching ${type}:`, err);
    } finally {
      setIsRefreshing(null);
    }
  };

  const getFieldType = (field: string): keyof Omit<DynamicOptions, 'lastUpdated'> | null => {
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

  const getOptionsForField = (field: string): { label: string; value: string }[] => {
    switch (field) {
      case 'whse':
        return dynamicOptions.warehouses.map(value => ({ label: value, value }));
      case 'category':
        return dynamicOptions.categories.map(value => ({ label: value, value }));
      case 'status':
        return dynamicOptions.statuses.map(value => ({ label: value, value }));
      case 'type':
        return dynamicOptions.types.map(value => ({ label: value, value }));
      default:
        return [];
    }
  };

  const getLastUpdated = (field: string) => {
    const type = getFieldType(field);
    if (!type) return __('Never', 'spire-sync');
    const date = dynamicOptions.lastUpdated[type];
    if (!date) return __('Never', 'spire-sync');
    return new Date(date).toLocaleString();
  };

  if (isLoading) {
    return <div>{__("Loading...", "spire-sync")}</div>;
  }

  return (
    <div className="spire-sync-settings-container">
      <h1>{__("Spire Sync Settings", "spire-sync")}</h1>

      {message && (
        <Notice status="success" isDismissible onDismiss={() => setMessage("")}>
          {message}
        </Notice>
      )}

      {error && (
        <Notice status="error" isDismissible onDismiss={() => setError("")}>
          {error}
        </Notice>
      )}

      <Panel header="Settings">
        <React.Fragment key="0">
          <PanelBody
            title={__("API Settings", "spire-sync")}
            initialOpen={true}
          >
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("Base URL", "spire-sync")}
                value={settings.base_url}
                onChange={(value) =>
                  setSettings({ ...settings, base_url: value || "" })
                }
                help={__(
                  "Enter the base URL for the Spire API (e.g., http://example.com/api/v2)",
                  "spire-sync"
                )}
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("Company Name", "spire-sync")}
                value={settings.company_name}
                onChange={(value) =>
                  setSettings({ ...settings, company_name: value || "" })
                }
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Username", "spire-sync")}
                value={settings.api_username}
                onChange={(value) =>
                  setSettings({ ...settings, api_username: value || "" })
                }
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Password", "spire-sync")}
                type="password"
                value={settings.api_password}
                onChange={(value) =>
                  setSettings({ ...settings, api_password: value || "" })
                }
                style={{ width: "400px" }}
              />
            </PanelRow>
          </PanelBody>

          <PanelBody
            title={__("Inventory Sync Settings", "spire-sync")}
            initialOpen={true}
          >
            <div className="spire-sync-conditions">
              <h3>{__("Sync Conditions", "spire-sync")}</h3>
              <RadioControl
                label={__("Match Type", "spire-sync")}
                selected={settings.inventory_sync?.match_type || 'all'}
                options={[
                  { label: __('All conditions must be met', 'spire-sync'), value: 'all' },
                  { label: __('Any condition must be met', 'spire-sync'), value: 'any' }
                ]}
                onChange={(value) => setSettings(prev => ({
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
                    match_type: value as 'all' | 'any'
                  }
                }))}
              />

              <div className="spire-sync-conditions-list">
                {(settings.inventory_sync?.conditions || []).map((condition, index) => (
                  <div key={index} className="spire-sync-condition">
                    <div className="spire-sync-condition-fields">
                      <SelectControl
                        label={__("Field", "spire-sync")}
                        value={condition.key}
                        options={[
                          { label: __('Select a field', 'spire-sync'), value: '' },
                          ...Object.entries(INVENTORY_FILTER_FIELDS).map(([key, label]) => ({
                            label,
                            value: key
                          }))
                        ]}
                        onChange={(value) => updateCondition(index, 'key', value)}
                      />
                      <SelectControl
                        label={__("Operator", "spire-sync")}
                        value={condition.operator}
                        options={[
                          { label: __('Select an operator', 'spire-sync'), value: '' },
                          ...Object.entries(CONDITION_OPERATORS).map(([key, label]) => ({
                            label,
                            value: key
                          }))
                        ]}
                        onChange={(value) => updateCondition(index, 'operator', value)}
                      />
                      <div className="spire-sync-condition-value">
                        <SelectControl
                          label={__("Value", "spire-sync")}
                          value={condition.value}
                          options={[
                            { label: __('Select a value', 'spire-sync'), value: '' },
                            ...getOptionsForField(condition.key)
                          ]}
                          onChange={(value) => updateCondition(index, 'value', value)}
                        />
                        {['whse', 'category', 'status', 'type'].includes(condition.key) && (
                          <div className="spire-sync-condition-value-actions">
                            <Button
                              isSmall
                              onClick={() => {
                                const type = getFieldType(condition.key);
                                if (type) {
                                  fetchDynamicOptions(type);
                                }
                              }}
                              disabled={isRefreshing === getFieldType(condition.key)}
                            >
                              {isRefreshing === getFieldType(condition.key) ? (
                                <Spinner />
                              ) : (
                                __('Refresh', 'spire-sync')
                              )}
                            </Button>
                            <span className="spire-sync-last-updated">
                              {__('Last updated:', 'spire-sync')} {getLastUpdated(condition.key)}
                            </span>
                          </div>
                        )}
                      </div>
                      <Button
                        isDestructive
                        onClick={() => removeCondition(index)}
                        className="spire-sync-remove-condition"
                      >
                        {__("Remove", "spire-sync")}
                      </Button>
                    </div>
                  </div>
                ))}
              </div>

              <Button
                isPrimary
                onClick={addCondition}
                className="spire-sync-add-condition"
              >
                {__("Add Condition", "spire-sync")}
              </Button>
            </div>

            <div className="spire-sync-filters">
              <h3>{__("Additional Filters", "spire-sync")}</h3>
              <InputControl
                __next40pxDefaultSize
                label={__("Warehouse Filter", "spire-sync")}
                value={settings.inventory_sync?.warehouse_filter || ''}
                onChange={(value) => setSettings(prev => ({
                  ...prev,
                  inventory_sync: {
                    ...prev.inventory_sync!,
                    warehouse_filter: value || ''
                  }
                }))}
                help={__("Optional: Filter by specific warehouse", "spire-sync")}
              />
              <InputControl
                __next40pxDefaultSize
                label={__("Category Filter", "spire-sync")}
                value={settings.inventory_sync?.category_filter || ''}
                onChange={(value) => setSettings(prev => ({
                  ...prev,
                  inventory_sync: {
                    ...prev.inventory_sync!,
                    category_filter: value || ''
                  }
                }))}
                help={__("Optional: Filter by specific category", "spire-sync")}
              />
            </div>

            <div className="spire-sync-schedule">
              <h3>{__("Sync Schedule", "spire-sync")}</h3>
              <SelectControl
                label={__("Sync Interval", "spire-sync")}
                value={settings.inventory_sync?.sync_interval || 'hourly'}
                options={[
                  { label: __('Hourly', 'spire-sync'), value: 'hourly' },
                  { label: __('Daily', 'spire-sync'), value: 'daily' },
                  { label: __('Weekly', 'spire-sync'), value: 'weekly' }
                ]}
                onChange={(value) => setSettings(prev => ({
                  ...prev,
                  inventory_sync: {
                    ...prev.inventory_sync!,
                    sync_interval: value as 'hourly' | 'daily' | 'weekly'
                  }
                }))}
              />
            </div>

            <div className="spire-sync-status">
              <h3>{__("Sync Status", "spire-sync")}</h3>
              <p>
                {__("Last Sync:", "spire-sync")} {settings.inventory_sync?.last_sync || __('Never', 'spire-sync')}
              </p>
              <p>
                {__("Status:", "spire-sync")} {settings.inventory_sync?.sync_status || 'idle'}
              </p>
              {settings.inventory_sync?.error_message && (
                <Notice status="error" isDismissible={false}>
                  {settings.inventory_sync.error_message}
                </Notice>
              )}
            </div>
          </PanelBody>
        </React.Fragment>
      </Panel>

      <div className="spire-sync-actions">
        <Button variant="primary" onClick={handleSave} disabled={isSaving}>
          {isSaving
            ? __("Saving...", "spire-sync")
            : __("Save Settings", "spire-sync")}
        </Button>
        <Button variant="secondary" onClick={triggerSync} disabled={isSaving}>
          {isSaving
            ? __("Starting Sync...", "spire-sync")
            : __("Sync Now", "spire-sync")}
        </Button>
      </div>
    </div>
  );
};

export default SettingsPage;
