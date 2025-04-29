import { register, select, dispatch } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

/**
 * Action Types
 */
const SET_BASE_URL = "SET_BASE_URL";
const SET_COMPANY_NAME = "SET_COMPANY_NAME";
const SET_API_USERNAME = "SET_API_USERNAME";
const SET_API_PASSWORD = "SET_API_PASSWORD";
const RECEIVE_SETTINGS = "RECEIVE_SETTINGS";

/**
 * Initial State
 */
const DEFAULT_STATE = {
  baseUrl: "",
  companyName: "",
  apiUsername: "",
  apiPassword: "",
};

/**
 * Reducer
 */
interface State {
  baseUrl: string;
  companyName: string;
  apiUsername: string;
  apiPassword: string;
}

interface Action {
  type: string;
  payload?: Partial<State>;
}

function reducer(state: State = DEFAULT_STATE, action: Action): State {
  switch (action.type) {
    case SET_BASE_URL:
    case SET_COMPANY_NAME:
    case SET_API_USERNAME:
    case SET_API_PASSWORD:
    case RECEIVE_SETTINGS:
      return {
        ...state,
        ...action.payload,
      };
    default:
      return state;
  }
}

/**
 * Action Creators
 */
export const actions = {
  setBaseUrl(value: string) {
    return { type: SET_BASE_URL, payload: { baseUrl: value } };
  },
  setCompanyName(value: string) {
    return { type: SET_COMPANY_NAME, payload: { companyName: value } };
  },
  setApiUsername(value: string) {
    return { type: SET_API_USERNAME, payload: { apiUsername: value } };
  },
  setApiPassword(value: string) {
    return { type: SET_API_PASSWORD, payload: { apiPassword: value } };
  },
  receiveSettings(settings: Partial<State>) {
    return { type: RECEIVE_SETTINGS, payload: settings };
  },
};

/**
 * Controls (for async fetch/save)
 */
const controls = {
  FETCH_SETTINGS: ({ path }: { path: string }) => {
    return apiFetch({ path });
  },
  SAVE_SETTINGS: ({ path, data }: { path: string, data: Partial<State>}) => {
    return apiFetch({ path, method: "POST", data });
  },
};

/**
 * Action Creators for Async Operations
 */
export const fetchSettings = () => {
  return {
    type: "FETCH_SETTINGS",
    control: "FETCH_SETTINGS",
    payload: {
      path: "/wp/v2/settings",
    },
  };
};

export const saveSettings = (settings: Partial<State>) => {
  return {
    type: "SAVE_SETTINGS",
    control: "SAVE_SETTINGS",
    payload: {
      path: "/wp/v2/settings",
      data: { spire_sync: settings },
    },
  };
};

/**
 * Selectors
 */
export const selectors = {
  getBaseUrl(state: State) {
    return state.baseUrl;
  },
  getCompanyName(state: State) {
    return state.companyName;
  },
  getApiUsername(state: State) {
    return state.apiUsername;
  },
  getApiPassword(state: State) {
    return state.apiPassword;
  },
};

/**
 * Register the store
 */
export const STORE_KEY = "spire-sync/settings";

register(STORE_KEY, {
  reducer,
  actions,
  selectors,
  controls,
});
