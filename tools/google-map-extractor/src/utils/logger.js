// Simple terminal logger with colors & timestamps
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  dim: '\x1b[2m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m',
  white: '\x1b[37m',
  gray: '\x1b[90m',
};

function timestamp() {
  return new Date().toLocaleTimeString('id-ID', { hour12: false });
}

export const logger = {
  info(msg, ...args) {
    console.log(`${colors.gray}[${timestamp()}]${colors.reset} ${colors.blue}ℹ${colors.reset} ${msg}`, ...args);
  },
  success(msg, ...args) {
    console.log(`${colors.gray}[${timestamp()}]${colors.reset} ${colors.green}✔${colors.reset} ${colors.green}${msg}${colors.reset}`, ...args);
  },
  warn(msg, ...args) {
    console.log(`${colors.gray}[${timestamp()}]${colors.reset} ${colors.yellow}⚠${colors.reset} ${colors.yellow}${msg}${colors.reset}`, ...args);
  },
  error(msg, ...args) {
    console.error(`${colors.gray}[${timestamp()}]${colors.reset} ${colors.red}✖${colors.reset} ${colors.red}${msg}${colors.reset}`, ...args);
  },
  step(stepNum, total, msg) {
    console.log(`${colors.gray}[${timestamp()}]${colors.reset} ${colors.cyan}[${stepNum}/${total}]${colors.reset} ${colors.bright}${msg}${colors.reset}`);
  },
  banner(title) {
    console.log(`\n${colors.cyan}${colors.bright}══════════════════════════════════════════════════════════════${colors.reset}`);
    console.log(`  ${colors.bright}${title}${colors.reset}`);
    console.log(`${colors.cyan}══════════════════════════════════════════════════════════════${colors.reset}\n`);
  }
};
