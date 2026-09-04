<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class ServerMonitorCode extends Controller {

    public function index() {

        if(!settings()->monitors_heartbeats->server_monitors_is_enabled) {
            redirect('dashboard');
        }

        $server_monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $api_key = isset($this->params[1]) ? input_clean($this->params[1]) : null;

        if(!$server_monitor_id || !$api_key) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor ID or API key is missing.");
        }

        /* Get the user */
        $user = \Altum\Cache::cache_function_result('user?api_key=' . $api_key, null, function() use ($api_key, $server_monitor_id) {
            return db()->where('api_key', $api_key)->where('status', 1)->getOne('users');
        });

        if(!$user) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor owner not found.");
        }

        if($user->status != 1) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor owner is disabled.");
        }

        $user->plan_settings = json_decode($user->plan_settings ?? '');

        /* Get the server monitor */
        $server_monitor = \Altum\Cache::cache_function_result('server_monitor?server_monitor_id=' . $server_monitor_id, null, function() use ($user, $server_monitor_id) {
            return db()->where('server_monitor_id', $server_monitor_id)->where('user_id', $user->user_id)->getOne('server_monitors');
        });

        /* Get the server monitor */
        if(!$server_monitor) {
            die(settings()->main->title . " (" . SITE_URL. "): Server monitor not found.");
        }

        $url = SITE_URL . 'server-monitor-track/$server_id/$api_key';

        $os = strtolower(trim($_GET['os'] ?? 'linux'));

        if(!in_array($os, ['windows', 'macos', 'linux'])) {
            die(settings()->main->title . " (" . SITE_URL. "): Improper OS specified.");
        }

        /* Set header */
        header('Content-Type: text/plain; charset=utf-8');

        if($os === 'windows') {
            echo <<<ALTUM
# PowerShell script for Windows monitoring

\$server_id = $server_monitor_id
\$api_key = '$user->api_key'
\$url = "$url"

# CPU usage
\$cpu_usage = (Get-Counter '\\Processor(_Total)\\% Processor Time').CounterSamples.CookedValue
\$cpu_usage = [math]::Round(\$cpu_usage, 2)

# RAM
\$mem = Get-CimInstance Win32_OperatingSystem
\$ram_total = [math]::Round(\$mem.TotalVisibleMemorySize / 1024)
\$ram_used = [math]::Round((\$mem.TotalVisibleMemorySize - \$mem.FreePhysicalMemory) / 1024)
\$ram_usage = [math]::Round((\$ram_used / \$ram_total) * 100, 2)

# Disk (C:)
\$disk = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'"
\$disk_total = [math]::Round(\$disk.Size / 1MB)
\$disk_used = [math]::Round((\$disk.Size - \$disk.FreeSpace) / 1MB)
\$disk_usage = [math]::Round((\$disk_used / \$disk_total) * 100, 2)

# OS and kernel
\$os_info = Get-CimInstance Win32_OperatingSystem
\$os_name = \$os_info.Caption
\$os_version = \$os_info.Version
\$kernel_name = "Windows"
\$kernel_release = \$os_info.BuildNumber
\$kernel_version = \$os_info.Version

# CPU info
\$cpu = Get-CimInstance Win32_Processor
\$cpu_model = \$cpu.Name
\$cpu_cores = \$cpu.NumberOfLogicalProcessors
\$cpu_frequency = \$cpu.MaxClockSpeed
\$cpu_architecture = \$cpu.AddressWidth

# Uptime
\$boot_time = \$os_info.LastBootUpTime
\$uptime = [math]::Round((New-TimeSpan -Start \$boot_time -End (Get-Date)).TotalSeconds)

# Network live rates
\$net_stats = Get-Counter '\\Network Interface(*)\\Bytes Received/sec','\\Network Interface(*)\\Bytes Sent/sec'
\$download_speed = [math]::Round((\$net_stats.CounterSamples | Where-Object { \$_.Path -like '*Received*' } | Measure-Object CookedValue -Sum).Sum)
\$upload_speed = [math]::Round((\$net_stats.CounterSamples | Where-Object { \$_.Path -like '*Sent*' } | Measure-Object CookedValue -Sum).Sum)

# Total counters not supported directly
\$network_total_download = 0
\$network_total_upload = 0

# JSON output
\$data = @{
    "network_download" = "\$download_speed"
    "network_upload" = "\$upload_speed"
    "network_total_download" = "\$network_total_download"
    "network_total_upload" = "\$network_total_upload"
    "cpu_model" = "\$cpu_model"
    "cpu_cores" = "\$cpu_cores"
    "cpu_frequency" = "\$cpu_frequency"
    "uptime" = "\$uptime"
    "os_name" = "\$os_name"
    "os_version" = "\$os_version"
    "kernel_name" = "\$kernel_name"
    "kernel_release" = "\$kernel_release"
    "kernel_version" = "\$kernel_version"
    "cpu_architecture" = "\$cpu_architecture"
    "cpu_usage" = "\$cpu_usage"
    "ram_usage" = "\$ram_usage"
    "ram_total" = "\$ram_total"
    "ram_used" = "\$ram_used"
    "disk_usage" = "\$disk_usage"
    "disk_total" = "\$disk_total"
    "disk_used" = "\$disk_used"
} | ConvertTo-Json -Compress

Invoke-RestMethod -Uri \$url -Method POST -ContentType "application/json" -Body \$data

ALTUM;
        }

        elseif($os === 'macos') {
            /* macOS monitoring script */
            echo <<<ALTUM
#!/bin/zsh

export PATH="/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin"
export LANG="en_US.UTF-8"
export LC_ALL=C

server_id=$server_monitor_id
api_key='$user->api_key'

# CPU usage (user + sys)
cpu_usage=$(/usr/bin/top -l 2 | /usr/bin/grep "CPU usage" | /usr/bin/tail -1 | /usr/bin/awk -F'[:,% ]+' '{u=0;s=0; for(i=1;i<=NF;i++){ if(\$i=="user") u=$(i-1); if(\$i=="sys") s=$(i-1) } printf "%.2f", u+s }')

# CPU load averages (1, 5, 15)
read cpu_load_1 cpu_load_5 cpu_load_15 <<< $(/usr/sbin/sysctl -n vm.loadavg | /usr/bin/tr -d '{}' )

# RAM usage
ram_total=\$(/usr/sbin/sysctl -n hw.memsize | /usr/bin/awk '{print \$1 / 1024 / 1024}')
read page_size pages_used <<< $(/usr/bin/vm_stat | /usr/bin/awk '
/page size of/ {gsub(/[^0-9]/,"",$8); ps=$8}
/Pages (active|wired down|speculative|occupied by compressor|throttled|purgeable|inactive):/ {gsub(/\./,"",$3); sum+=$3}
END {print ps, sum}')
ram_used=$((pages_used * page_size / 1024 / 1024))
ram_usage=\$(/usr/bin/awk -v used="\$ram_used" -v total="\$ram_total" 'BEGIN { printf "%.2f", (used / total) * 100 }')

# Disk usage (requires root for APFS via diskutil)
disk_total_bytes=\$(/usr/sbin/diskutil info / | /usr/bin/grep -E 'Container Total Space|Volume Total Space' | /usr/bin/head -n1 | /usr/bin/awk -F'[()]' '{gsub(/[^0-9]/, "", \$2); print \$2}')
disk_free_bytes=\$(/usr/sbin/diskutil info / | /usr/bin/grep -E 'Container Free Space|Volume Free Space' | /usr/bin/head -n1 | /usr/bin/awk -F'[()]' '{gsub(/[^0-9]/, "", \$2); print \$2}')

if [[ "\$disk_total_bytes" =~ '^[0-9]+\$' ]] && [[ "\$disk_free_bytes" =~ '^[0-9]+\$' ]]; then
    disk_used_bytes=\$((disk_total_bytes - disk_free_bytes))
    disk_total=\$(/usr/bin/awk -v b="\$disk_total_bytes" 'BEGIN { printf "%.2f", b / 1024 / 1024 }')
    disk_used=\$(/usr/bin/awk -v b="\$disk_used_bytes" 'BEGIN { printf "%.2f", b / 1024 / 1024 }')
    disk_usage=\$(/usr/bin/awk -v used="\$disk_used" -v total="\$disk_total" 'BEGIN { if(total == 0) print "N/A"; else printf "%.2f", (used / total) * 100 }')
else
    disk_total="N/A"
    disk_used="N/A"
    disk_usage="N/A"
fi

# OS info
os_name=\$(/usr/bin/sw_vers -productName)
os_version=\$(/usr/bin/sw_vers -productVersion)

# Kernel info
kernel_name=\$(/usr/bin/uname -s)
kernel_release=\$(/usr/bin/uname -r)
kernel_version=\$(/usr/bin/uname -v)

# CPU info
cpu_architecture=\$(/usr/bin/uname -m)
cpu_model=\$(/usr/sbin/sysctl -n machdep.cpu.brand_string)
cpu_cores=\$(/usr/sbin/sysctl -n hw.ncpu)
cpu_frequency=\$(/usr/sbin/sysctl -n hw.cpufrequency 2>/dev/null | /usr/bin/awk '{ freq=\$1 / 1000000; if(freq == 0) print "N/A"; else printf "%.0f", freq }')

# Uptime (fixed)
boot_time=\$(/usr/sbin/sysctl -n kern.boottime | /usr/bin/awk -F'[{},=]' '{print \$3}' | /usr/bin/tr -d ' ')
current_time=\$(/bin/date +%s)
uptime=\$((current_time - boot_time))

# Network totals
read_net_sums() {
  /usr/sbin/netstat -ib | /usr/bin/awk 'NR>1 && $1!="lo0" {rx+=$7; tx+=$10} END {print rx+0, tx+0}'
}

read initial_recv initial_sent <<< $(read_net_sums)
interval=3
/bin/sleep \$interval
read final_recv final_sent <<< $(read_net_sums)

network_download=$(((final_recv - initial_recv) / interval))
network_upload=$(((final_sent - initial_sent) / interval))

network_total_download=\$final_recv
network_total_upload=\$final_sent

# Create JSON payload
json="{\\"network_download\\": \\"\$network_download\\", \\"network_upload\\": \\"\$network_upload\\", \\"network_total_download\\": \\"\$network_total_download\\", \\"network_total_upload\\": \\"\$network_total_upload\\", \\"cpu_model\\": \\"\$cpu_model\\", \\"cpu_cores\\": \\"\$cpu_cores\\", \\"cpu_frequency\\": \\"\$cpu_frequency\\", \\"uptime\\": \\"\$uptime\\", \\"os_name\\": \\"\$os_name\\", \\"os_version\\": \\"\$os_version\\", \\"kernel_name\\": \\"\$kernel_name\\", \\"kernel_release\\": \\"\$kernel_release\\", \\"kernel_version\\": \\"\$kernel_version\\", \\"cpu_architecture\\": \\"\$cpu_architecture\\", \\"cpu_usage\\": \\"\$cpu_usage\\", \\"ram_usage\\": \\"\$ram_usage\\", \\"ram_total\\": \\"\$ram_total\\", \\"ram_used\\": \\"\$ram_used\\", \\"disk_usage\\": \\"\$disk_usage\\", \\"disk_total\\": \\"\$disk_total\\", \\"disk_used\\": \\"\$disk_used\\", \\"cpu_load_1\\": \\"\$cpu_load_1\\", \\"cpu_load_5\\": \\"\$cpu_load_5\\", \\"cpu_load_15\\": \\"\$cpu_load_15\\"}"

# Send JSON to server
url="$url"
/usr/bin/curl -X POST -H "Content-Type: application/json" -d "\$json" "\$url"

# Optional debug logging
# echo "\$json" >> /tmp/server_monitor_debug.log 2>&1
ALTUM;

        } else {

            /* Default Linux monitoring script */
            echo <<<ALTUM
#!/bin/bash
LC_ALL=C

server_id=$server_monitor_id
api_key='$user->api_key'

# Calculate CPU usage
cpu_usage=$(HOME=/dev/null top -bn2 | grep "Cpu(s)" | tail -n 1 | awk '{printf "%.2f\\n", $2 + $4}')

# Use awk to parse the load averages
read -r cpu_load_1 cpu_load_5 cpu_load_15 _ < /proc/loadavg

# Collect RAM statistics
read -r ram_total_kb ram_used_kb ram_usage < <(
  awk '
    /^MemTotal:/ {t=$2}
    /^MemAvailable:/ {a=$2}
    END {
      u=t-a
      if (t<=0) p="0.00"; else p=sprintf("%.2f", (u/t)*100)
      print t, u, p
    }
  ' /proc/meminfo
)
ram_total=$((ram_total_kb/1024))
ram_used=$((ram_used_kb/1024))

# Collect disk usage
read -r disk_total_kb disk_used_kb disk_usage < <(df -Pk / | awk 'NR==2 {t=$2; u=$3; if (t<=0) p="0.00"; else p=sprintf("%.2f", (u/t)*100); print t, u, p}')
disk_total=$((disk_total_kb/1024))
disk_used=$((disk_used_kb/1024))

# Extract OS Name and Version from /etc/os-release
os_name=''
os_version=''
if [ -r /etc/os-release ]; then
  . /etc/os-release
  os_name="\$NAME"
  os_version="\$VERSION"
fi

cpu_architecture=$(uname -m)

IFS='|' read -r cpu_model cpu_cores cpu_frequency < <(
  awk -F': *' '
    /^model name/ && !cpu_model {
      cpu_model=$2
      sub(/^[[:space:]]+/, "", cpu_model)
    }
    /^processor/ { cpu_cores++ }
    /^cpu MHz/ && !cpu_frequency { cpu_frequency=$2 }
    END {
      printf "%s|%s|%s\\n", cpu_model, cpu_cores+0, cpu_frequency
    }
  ' /proc/cpuinfo
)

# Uptime
read -r uptime _ < /proc/uptime

# Function to read current network stats
read_net_sums() {
  awk -F'[: ]+' 'NR>2 && $1!="lo" {rx+=$3; tx+=$11} END {print rx+0, tx+0}' /proc/net/dev
}

read -r rx1 tx1 < <(read_net_sums)
interval=3
sleep \$interval
read -r rx2 tx2 < <(read_net_sums)

network_download=$(((rx2-rx1)/interval))
network_upload=$(((tx2-tx1)/interval))

# Initialize variables for total download and upload
read -r network_total_download network_total_upload < <(
  awk -F'[: ]+' 'NR>2 && $1!="lo" {rx+=$3; tx+=$11} END {print rx+0, tx+0}' /proc/net/dev
)

kernel_name=$(uname -s)
kernel_release=$(uname -r)
kernel_version=$(uname -v)

# Create JSON payload
json="{\"network_download\": \"\$network_download\", \"network_upload\": \"\$network_upload\", \"network_total_download\": \"\$network_total_download\", \"network_total_upload\": \"\$network_total_upload\",  \"cpu_model\": \"\$cpu_model\", \"cpu_cores\": \"\$cpu_cores\", \"cpu_frequency\": \"\$cpu_frequency\", \"uptime\": \"\$uptime\", \"os_name\": \"\$os_name\", \"os_version\": \"\$os_version\", \"kernel_name\": \"\$kernel_name\", \"kernel_release\": \"\$kernel_release\", \"kernel_version\": \"\$kernel_version\", \"cpu_architecture\": \"\$cpu_architecture\", \"cpu_usage\": \"\$cpu_usage\", \"ram_usage\": \"\$ram_usage\", \"ram_total\": \"\$ram_total\", \"ram_used\": \"\$ram_used\", \"disk_usage\": \"\$disk_usage\", \"disk_total\": \"\$disk_total\", \"disk_used\": \"\$disk_used\", \"cpu_load_1\": \"\$cpu_load_1\", \"cpu_load_5\": \"\$cpu_load_5\", \"cpu_load_15\": \"\$cpu_load_15\"}"

# Make HTTP POST request to upload data
url="$url"
curl -sS --max-time 10 -X POST -H 'Content-Type: application/json' -d "\$json" "\$url" >/dev/null 2>&1 &
ALTUM;
        }

    }
}
