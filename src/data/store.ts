import { createReduxStore, register } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";
import { DataRegistry } from "@wordpress/data/build-types/types";

export interface StoreState {
  base_url: string;
  company_name: string;
  api_username: string;
  api_password: string;
}

interface Action {
  type: string;
  payload?: Partial<StoreState>;
}

// This is the key used to register the store with WordPress
export const STORE_KEY = "spire-sync-store";

/** Action Types */
const SET_BASE_URL = "SET_BASE_URL";
const SET_COMPANY_NAME = "SET_COMPANY_NAME";
const SET_API_USERNAME = "SET_API_USERNAME";
const SET_API_PASSWORD = "SET_API_PASSWORD";
const RECEIVE_SETTINGS = "RECEIVE_SETTINGS";
const FETCH_SETTINGS = "FETCH_SETTINGS";
const SAVE_SETTINGS = "SAVE_SETTINGS";

/** Initial State */
const DEFAULT_STATE: StoreState = {
  base_url: "",
  company_name: "",
  api_username: "",
  api_password: "",
};

function reducer(
  state: StoreState = DEFAULT_STATE,
  action: Action
): StoreState {
  switch (action.type) {
    case SET_BASE_URL:
    case SET_COMPANY_NAME:
    case SET_API_USERNAME:
    case SET_API_PASSWORD:
    case RECEIVE_SETTINGS:
    case FETCH_SETTINGS:
    case SAVE_SETTINGS:
      return {
        ...state,
        ...action.payload,
      };
  }
  return state;
}

export const actions = {
  setBaseUrl(value: string): Action {
    return { type: SET_BASE_URL, payload: { base_url: value } };
  },
  setCompanyName(value: string): Action {
    return { type: SET_COMPANY_NAME, payload: { company_name: value } };
  },
  setApiUsername(value: string): Action {
    return { type: SET_API_USERNAME, payload: { api_username: value } };
  },
  setApiPassword(value: string): Action {
    return { type: SET_API_PASSWORD, payload: { api_password: value } };
  },
  receiveSettings(settings: Partial<StoreState>): Action {
    return { type: RECEIVE_SETTINGS, payload: settings };
  },
  fetchSettings(): Action {
    return { type: FETCH_SETTINGS };
  },
  saveSettings(settings: Partial<StoreState>): Action {
    return { type: SAVE_SETTINGS, payload: settings };
  },
};

export const selectors = {
  getBaseUrl(state: StoreState): string {
    return state.base_url;
  },
  getCompanyName(state: StoreState): string {
    return state.company_name;
  },
  getApiUsername(state: StoreState): string {
    return state.api_username;
  },
  getApiPassword(state: StoreState): string {
    return state.api_password;
  },
};

export const resolvers = {
  *fetchSettings(action: Action, registry: any) {
    const response: { spire_sync_settings: Partial<StoreState>} = yield apiFetch({
      path: "/wp/v2/settings",
    });
    const settings = response.spire_sync_settings || {};
    registry.dispatch(STORE_KEY).receiveSettings(settings);
  },

  *saveSettings(action: Action, registry: any) {
    const settings = action.payload as Partial<StoreState>;

    // 1) POST the new settings
    yield apiFetch({
      path: "/wp/v2/settings",
      method: "POST",
      data: {
        spire_sync_settings: settings,
      },
    });

    // 2) Re-fetch to sync state
    yield registry.dispatch(STORE_KEY).fetchSettings();
  },
};

const store = createReduxStore(STORE_KEY, {
  reducer,
  actions,
  selectors,
  resolvers,
});

register(store);

export const {
  setBaseUrl,
  setCompanyName,
  setApiUsername,
  setApiPassword,
  fetchSettings,
  saveSettings,
  receiveSettings,
} = actions;

export const { getBaseUrl, getCompanyName, getApiUsername, getApiPassword } =
  selectors;
