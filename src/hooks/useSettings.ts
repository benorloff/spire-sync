import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { store as noticesStore } from "@wordpress/notices";
import { useSelect, useDispatch } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";

interface SpireSyncSettings {
  base_url?: string;
  company_name?: string;
  api_username?: string;
  api_password?: string;
  sync_type?: "create" | "update" | "create-update" | "create-update-delete";
  sync_products?: boolean;
  sync_orders?: boolean;
  sync_customers?: boolean;
}

interface SpireSyncTestConnectionResponse {
  success: boolean;
  message: string;
}

const useSettings = () => {
  const [settings, setSettings] = useState({
    isSaving: false,
    isTesting: false,
    isValidConnection: false,
    message: "",
    wcVersion: "",
  });
  const [baseUrl, setBaseUrl] = useState<string>("");
  const [companyName, setCompanyName] = useState<string>("");
  const [apiUsername, setApiUsername] = useState<string>("");
  const [apiPassword, setApiPassword] = useState<string>("");
  const [syncType, setSyncType] = useState<
    "create" | "update" | "create-update" | "create-update-delete"
  >("update");
  const [syncProducts, setSyncProducts] = useState<boolean>(false);
  const [syncOrders, setSyncOrders] = useState<boolean>(false);
  const [syncCustomers, setSyncCustomers] = useState<boolean>(false);
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const [isTesting, setIsTesting] = useState<boolean>(false);
  const [message, setMessage] = useState<string>("");
  const [isValidConnection, setIsValidConnection] = useState<boolean>(false);
  const [wcVersion, setWcVersion] = useState<string>("");

  //   const { createSuccessNotice } = useDispatch(noticesStore);

  useEffect(() => {
    apiFetch({ path: "/wp/v2/settings" }).then((response) => {
      const settings = response as any;
      console.log("spire_sync_settings", settings.spire_sync_settings);
      setBaseUrl(settings.spire_sync_settings.base_url || "");
      setCompanyName(settings.spire_sync_settings.company_name || "");
      setApiUsername(settings.spire_sync_settings.api_username || "");
      setApiPassword(settings.spire_sync_settings.api_password || "");
      setSyncType(settings.spire_sync_settings.sync_type || "update");
      setSyncProducts(settings.spire_sync_settings.sync_products || false);
      setSyncOrders(settings.spire_sync_settings.sync_orders || false);
      setSyncCustomers(settings.spire_sync_settings.sync_customers || false);
    });
    apiFetch({ path: "/wc/v3/system_status" }).then((response) => {
      const system_status = response as any;
      const wcVers = system_status.environment.version ?? "";
      setWcVersion(wcVers);
    });
  }, []);

  //   const handleSave = async () => {
  //     setIsSaving(true);
  //     setMessage("");
  //     // const testConnection = await handleTestConnection();
  //   };

  const handleTestConnection = async ({
    base_url,
    company_name,
    api_username,
    api_password,
  }: {
    base_url: string;
    company_name: string;
    api_username: string;
    api_password: string;
  }) => {
    setIsTesting(true);
    setMessage("");

    let testConnectionResponse = {
      success: false,
      message: "",
    };

    apiFetch({
      path: "/spire_sync/v1/test-connection",
      method: "POST",
      data: {
        base_url,
        company_name,
        api_username,
        api_password,
      },
    })
      .then((response) => {
        testConnectionResponse = response as SpireSyncTestConnectionResponse;

        if (!testConnectionResponse.success) {
          setIsTesting(false);
          setIsValidConnection(false);
          setMessage(
            __("Invalid credentials. Please try again.", "spire-sync")
          );
          return;
        }
        setIsTesting(false);
        setIsValidConnection(true);
        setMessage(__("Connection successful!", "spire-sync"));
      })
      .catch((error) => {
        setIsTesting(false);
        setIsValidConnection(false);
        setMessage(
          __("An error occurred while testing the connection.", "spire-sync")
        );
        console.error(error);
      });
  };

  const saveSettings = async () => {
    setIsSaving(true);
    setMessage("");

    // Build the data object to send.
    apiFetch({
      path: "/wp/v2/settings",
      method: "POST",
      data: {
        spire_sync_settings: {
          base_url: baseUrl,
          company_name: companyName,
          api_username: apiUsername,
          api_password: apiPassword,
        },
      },
    })
      .then((response) => {
        console.log("Settings saved:", response);
        setIsSaving(false);
        setMessage(__("Settings saved successfully.", "spire-sync"));
        // Optionally, you can show a success notice using the notices store.
        // createSuccessNotice(__("Settings saved.", "spire-sync"));
      })
      .catch((error) => {
        setIsSaving(false);
        setMessage(__("Error saving settings.", "spire-sync"));
        console.error(error);
      });
  };

  return {
    settings,
    setSettings,
    baseUrl,
    setBaseUrl,
    companyName,
    setCompanyName,
    apiUsername,
    setApiUsername,
    apiPassword,
    setApiPassword,
    syncType,
    setSyncType,
    syncProducts,
    setSyncProducts,
    syncOrders,
    setSyncOrders,
    syncCustomers,
    setSyncCustomers,
    message,
    setMessage,
    isTesting,
    setIsTesting,
    isValidConnection,
    setIsValidConnection,
    wcVersion,
    setWcVersion,
    isSaving,
    setIsSaving,
    handleTestConnection,
    saveSettings,
  };
};

export default useSettings;
