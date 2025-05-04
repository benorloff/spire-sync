import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { store as noticesStore } from "@wordpress/notices";
import { useSelect, useDispatch } from "@wordpress/data";
import { useEffect, useState } from "@wordpress/element";
import { STORE_KEY } from "../data/store";

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
  const [isTesting, setIsTesting] = useState<boolean>(false);
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const [message, setMessage] = useState<string>("");
  const [isValidConnection, setIsValidConnection] = useState<boolean>(false);
  const [wcVersion, setWcVersion] = useState<string>("");

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

  const { fetchSettings } = useDispatch(STORE_KEY);

  useEffect(() => {
    fetchSettings();
    // Fetch WooCommerce version
    apiFetch({ path: "/wc/v3/system_status" }).then((response) => {
      const system_status = response as any;
      const wcVers = system_status.environment.version ?? "";
      setWcVersion(wcVers);
    });
    console.log("Settings from useSettings store select: ", {base_url, company_name, api_username, api_password})

  }, []);

  const handleTestConnection = async (credentials: {
    base_url: string;
    company_name: string;
    api_username: string;
    api_password: string;
  }) => {
    setIsTesting(true);
    setMessage("");

    try {
      const response: SpireSyncTestConnectionResponse = await apiFetch({
        path: "/spire_sync/v1/test-connection",
        method: "POST",
        data: credentials,
      });

      setIsValidConnection(response.success);
      setMessage(response.message);
      return response.success;
    } catch (error) {
      setIsValidConnection(false);
      setMessage(__("Error testing connection.", "spire-sync"));
      throw error;
    } finally {
      setIsTesting(false);
    }
  };

  return {
    message,
    setMessage,
    isTesting,
    isSaving,
    setIsSaving,
    isValidConnection,
    wcVersion,
    handleTestConnection,
  };
};

export default useSettings;
