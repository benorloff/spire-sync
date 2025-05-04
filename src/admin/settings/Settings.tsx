import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import {
  Panel,
  PanelBody,
  PanelRow,
  __experimentalInputControl as InputControl,
  Button,
  Notice,
} from "@wordpress/components";
import apiFetch from "@wordpress/api-fetch";

interface Settings {
  base_url: string;
  company_name: string;
  api_username: string;
  api_password: string;
}

interface SettingsResponse {
  spire_sync_settings: Settings;
}

interface EncryptionResponse {
  success: boolean;
  data: string;
}

const SettingsPage: React.FC = () => {
  const [settings, setSettings] = useState<Settings>({
    base_url: "",
    company_name: "",
    api_username: "",
    api_password: "",
  });
  const [isLoading, setIsLoading] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

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
        </React.Fragment>
      </Panel>
      <Button variant="primary" onClick={handleSave} disabled={isSaving}>
        {isSaving
          ? __("Saving...", "spire-sync")
          : __("Save Settings", "spire-sync")}
      </Button>
    </div>
  );
};

export default SettingsPage;
