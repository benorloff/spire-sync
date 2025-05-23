import React from "react";
import { __ } from "@wordpress/i18n";
import {
  Panel,
  PanelBody,
  PanelRow,
  TextControl,
  Button,
  Notice,
  __experimentalInputControl as InputControl,
  __experimentalInputControlSuffixWrapper as InputControlSuffixWrapper,
} from "@wordpress/components";
import { more } from "@wordpress/icons";
import useSettings from "../../hooks/useSettings";

const SettingsPage: React.FC = () => {
  const {
    baseUrl,
    setBaseUrl,
    companyName,
    setCompanyName,
    apiUsername,
    setApiUsername,
    apiPassword,
    setApiPassword,
    isTesting,
    setIsTesting,
    isSaving,
    setIsSaving,
    message,
    setMessage,
    handleSave,
    handleTestConnection,
    saveSettings,
  } = useSettings();

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
                value={baseUrl}
                onChange={(val: string | undefined) => setBaseUrl(val || "")}
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
                value={companyName}
                onChange={(val: string | undefined) =>
                  setCompanyName(val || "")
                }
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Username", "spire-sync")}
                value={apiUsername}
                onChange={(val: string | undefined) =>
                  setApiUsername(val || "")
                }
                style={{ width: "400px" }}
              />
            </PanelRow>
            <PanelRow>
              <InputControl
                __next40pxDefaultSize
                label={__("API Password", "spire-sync")}
                type="password"
                value={apiPassword}
                onChange={(val: string | undefined) =>
                  setApiPassword(val || "")
                }
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
      <Button variant="primary" onClick={saveSettings} disabled={isSaving}>
        {isSaving
          ? __("Saving...", "spire-sync")
          : __("Save Settings", "spire-sync")}
      </Button>
    </div>
  );
};

export default SettingsPage;
