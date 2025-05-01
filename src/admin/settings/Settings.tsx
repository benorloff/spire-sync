import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";
import {
  Panel,
  PanelBody,
  PanelRow,
  TextControl,
  Button,
  Notice,
  NavigableMenu,
  __experimentalInputControl as InputControl,
  __experimentalInputControlSuffixWrapper as InputControlSuffixWrapper,
  SelectControl,
  ToggleControl,
  FormToggle,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
  CheckboxControl,
  Flex,
  FlexItem,
  BaseControl,
  Tooltip,
  Icon,
} from "@wordpress/components";
import { more } from "@wordpress/icons";
import { useSelect, useDispatch } from "@wordpress/data";
import { STORE_KEY } from "../../data/store";
import useSettings from "../../hooks/useSettings";

const SettingsPage: React.FC = () => {
  const { base_url, company_name, api_username, api_password } = useSelect(
    (select) => {
      const store = select(STORE_KEY) as {
        getBaseUrl: () => string;
        getCompanyName: () => string;
        getApiUsername: () => string;
        getApiPassword: () => string;
      };
      return {
        base_url: store.getBaseUrl(),
        company_name: store.getCompanyName(),
        api_username: store.getApiUsername(),
        api_password: store.getApiPassword(),
      };
    },
    []
  );

  const {
    setBaseUrl,
    setCompanyName,
    setApiUsername,
    setApiPassword,
    saveSettings,
    fetchSettings,
  } = useDispatch(STORE_KEY);

  useEffect(() => {
    fetchSettings();
  }, []);

  useEffect(() => {
    console.log("Settings loaded:", { base_url, company_name, api_username });
  }, [base_url, company_name, api_username]);

  const {
    message,
    setMessage,
    isTesting,
    isSaving,
    setIsSaving,
    isValidConnection,
    wcVersion,
    handleTestConnection,
  } = useSettings();

  const [checked, setChecked] = useState<boolean>(true);

  const handleSave = async () => {
    setMessage("");
    // First test credentials
    try {
      const success = await handleTestConnection({
        base_url,
        company_name,
        api_username,
        api_password,
      });
    } catch (error) {
      console.error("Error testing connection:", error);
      setMessage(
        __(
          "Error testing connection. Please check your credentials.",
          "spire-sync"
        )
      );
      return;
    }

    // Only if test passed do we save
    console.log("Attempting to save settings...");
    saveSettings({ base_url, company_name, api_username, api_password });
    setMessage(__('Settings saved successfully.', 'spire-sync'));
  };

  return (
    <div className="spire-sync-settings-container">
      <h1>{__("Spire Sync Settings", "spire-sync")}</h1>
      {message && (
        <Notice
          status="success"
          isDismissible={true}
          onDismiss={() => setMessage("")}
        >
          {message}
        </Notice>
      )}

      {/* <NavigableMenu orientation={"vertical"}>
        <Button variant="tertiary">Item 1</Button>
        <Button variant="secondary">Item 2</Button>
        <Button variant="secondary">Item 3</Button>
      </NavigableMenu> */}
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
                value={base_url}
                onChange={(value) => setBaseUrl(value || "")}
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
                value={company_name}
                onChange={(value) => setCompanyName(value || "")}
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Username", "spire-sync")}
                value={api_username}
                onChange={(value) => setApiUsername(value || "")}
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Password", "spire-sync")}
                type="password"
                value={api_password}
                onChange={(value) => setApiPassword(value || "")}
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <Button
                variant="secondary"
                onClick={handleTestConnection}
                disabled={isTesting}
              >
                {isTesting
                  ? __("Testing...", "spire-sync")
                  : __("Test Connection", "spire-sync")}
              </Button>
            </PanelRow>
          </PanelBody>
        </React.Fragment>
      </Panel>
      <Card>
        <CardHeader>Inventory Settings</CardHeader>
        <CardBody>
          <Flex>
            <FlexItem>
              Enable Inventory Sync
              <Tooltip
                placement="right-end"
                text="Enable this setting to sync inventory items from Spire to WooCommerce products."
              >
                <Icon icon={"info-outline"} style={{ paddingLeft: "10px" }} />
              </Tooltip>
            </FlexItem>
            <FlexItem>
              <ToggleControl
                __nextHasNoMarginBottom
                label=""
                checked={checked}
                onChange={() => setChecked((state) => !state)}
              />
            </FlexItem>
          </Flex>
        </CardBody>
      </Card>
      {/* <PanelBody
            title={__("Sync Settings", "spire-sync")}
            initialOpen={true}
          > */}
      {/* <PanelRow>
              <SelectControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                label={__("Sync Type", "spire-sync")}
                value={settings.spire_sync_settings.sync_options?.type}
                options={[
                  { label: __("Create Only", "spire-sync"), value: "create" },
                  { label: __("Update Only", "spire-sync"), value: "update" },
                  {
                    label: __("Create & Update", "spire-sync"),
                    value: "create-update",
                  },
                  {
                    label: __("Create, Update, & Delete", "spire-sync"),
                    value: "create-update-delete",
                  },
                ]}
                onChange={(value) =>
                  setSettings({
                    ...settings,
                    spire_sync_settings: {
                      ...settings.spire_sync_settings,
                      sync_options: {
                        ...settings.spire_sync_settings.sync_options,
                        type: value || "update",
                      },
                    },
                  })
                }
                style={{ width: "400px" }}
              />
            </PanelRow> */}
      {/* <PanelRow>
              <p>Sync Products</p>
              <FormToggle
                checked={
                  settings.spire_sync_settings.sync_options?.sync_products
                }
                onChange={() =>
                  setSettings({
                    ...settings,
                    spire_sync_settings: {
                      ...settings.spire_sync_settings,
                      sync_options: {
                        ...settings.spire_sync_settings.sync_options,
                        sync_products:
                          !settings.spire_sync_settings.sync_options
                            .sync_products,
                      },
                    },
                  })
                }
              />
            </PanelRow> */}
      {/* </PanelBody>
        </React.Fragment>
      </Panel> */}
      {/* <Card>
        <CardHeader>
          <h2>Spire API</h2>
        </CardHeader>
        <CardBody>
          <InputControl
            __next40pxDefaultSize
            label={__("Base URL", "spire-sync")}
            value={settings.spire_sync_settings.spire_api?.base_url}
            onChange={(value) =>
              setSettings({
                ...settings,
                spire_sync_settings: {
                  ...settings.spire_sync_settings,
                  spire_api: {
                    ...settings.spire_sync_settings.spire_api,
                    base_url: value || "",
                  },
                },
              })
            }
            help={__(
              "Enter the base URL for the Spire API (e.g., http://example.com/api/v2)",
              "spire-sync"
            )}
          />
        </CardBody>
        <CardFooter>
          <Button
            variant="primary"
            onClick={saveSettings}
            disabled={isSaving}
          >
            {isSaving
              ? __("Saving...", "spire-sync")
              : __("Save Settings", "spire-sync")}
          </Button>
        </CardFooter>
      </Card> */}
      {/* <div>
        <h1>Status</h1>
        <p>Version: {wcVersion}</p>
      </div> */}
      <Button variant="primary" onClick={handleSave} disabled={isSaving}>
        {isSaving
          ? __("Saving...", "spire-sync")
          : __("Save Settings", "spire-sync")}
      </Button>
    </div>
  );
};

export default SettingsPage;
