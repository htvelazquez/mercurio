// Tasks statuses
const STATUS_CREATED = "created";
const STATUS_DONE = "done";
const STATUS_NO_RESPONSE = "no-response";
const STATUS_ERROR = "error";
const STATUS_AUTH_ERROR = "auth-error";
const STATUS_CAP_REACHED = "cap-reached";
const STATUS_UNAVAILABLE = "unavailable";
const STATUS_LIMIT_EXCEEDED = "limit-exceeded";



// Control actions
const CTRL_START = "start";
const CTRL_PAUSE = "pause";
const CTRL_RESUME = "resume";
const CTRL_GET_DATA = "getData";
const CTRL_NEW_TAB = "newTab";
const CTRL_LOGIN = "login";
const CTRL_LOGOUT = "logout";
const CTRL_REDIRECT = "redirect";


// Default Configs
// Max attempt to stop running
const RETRIES_ERROR = 3;
const TIMES_TO_RETRIES = 20;

// timeout time (milliseconds)
const RETRY_TIME = 20000;

// timeout getTasks() time (milliseconds)
const RETRY_GET_TASKS = 120000;

// time to close after done + random time
const TIME_AFTER_DONE = 3000;

const LOGIN_LINKEDIN_URL = 'https://www.linkedin.com/uas/login';

// Grabber API
// GRABBER_URL
//const GRABBER_URL = 'http://localhost:8014/api';
const GRABBER_URL = 'https://grabber-jobs.mercurio.com/api';

// Sentry
// SENTRY_URL
const SENTRY_URL = 'https://1e04e9346a3146789a46d9d98b901920@sentry.io/180128';
