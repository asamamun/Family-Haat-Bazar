# Response Rate & Load Testing Guide
## Family Haat Bazar Performance Testing

### 🎯 **Objective**
Test the response time and performance of `https://coders64.xyz/projects/haatbazar/` under different load conditions to identify bottlenecks and optimize performance.

---

## 📊 **Testing Methods & Tools**

### **Method 1: Apache Bench (ab) - Simple & Quick**

#### **Installation:**
```bash
# Ubuntu/Debian
sudo apt-get install apache2-utils

# CentOS/RHEL
sudo yum install httpd-tools

# macOS
brew install apache2
```

#### **Basic Load Tests:**
```bash
# Test with 500 requests, 50 concurrent users
ab -n 500 -c 50 https://coders64.xyz/projects/haatbazar/

# Test with 5000 requests, 100 concurrent users
ab -n 5000 -c 100 https://coders64.xyz/projects/haatbazar/

# Test specific pages
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/product-details.php?id=42

# Test with custom headers (simulate real browsers)
ab -n 1000 -c 50 -H "User-Agent: Mozilla/5.0" https://coders64.xyz/projects/haatbazar/
```

#### **Advanced Apache Bench Commands:**
```bash
# Test with keep-alive connections
ab -n 2000 -c 100 -k https://coders64.xyz/projects/haatbazar/

# Test with timeout settings
ab -n 1000 -c 50 -s 30 https://coders64.xyz/projects/haatbazar/

# Save results to file
ab -n 1000 -c 50 -g results.tsv https://coders64.xyz/projects/haatbazar/
```

---

### **Method 2: Artillery.js - Advanced Load Testing**

#### **Installation:**
```bash
npm install -g artillery
```

#### **Create Test Configuration:**
Create `load-test-config.yml`:
```yaml
config:
  target: 'https://coders64.xyz/projects/haatbazar'
  phases:
    - duration: 60
      arrivalRate: 10
      name: "Warm up"
    - duration: 120
      arrivalRate: 50
      name: "Ramp up load"
    - duration: 300
      arrivalRate: 100
      name: "Sustained load"
  defaults:
    headers:
      User-Agent: "Artillery Load Test"

scenarios:
  - name: "Homepage Load Test"
    weight: 40
    flow:
      - get:
          url: "/"
      - think: 2
      
  - name: "Product Details Test"
    weight: 30
    flow:
      - get:
          url: "/product-details.php?id=42"
      - think: 3
      
  - name: "Category Browse Test"
    weight: 20
    flow:
      - get:
          url: "/index.php?category=5"
      - think: 2
      
  - name: "Search Test"
    weight: 10
    flow:
      - get:
          url: "/apis/get-products.php?category=5"
      - think: 1
```

#### **Run Artillery Tests:**
```bash
# Run the configuration
artillery run load-test-config.yml

# Quick test (500 requests per minute)
artillery quick --count 500 --num 60 https://coders64.xyz/projects/haatbazar/

# Generate HTML report
artillery run load-test-config.yml --output report.json
artillery report report.json
```

---

### **Method 3: wrk - High Performance Testing**

#### **Installation:**
```bash
# Ubuntu/Debian
sudo apt-get install wrk

# macOS
brew install wrk

# From source
git clone https://github.com/wg/wrk.git
cd wrk
make
sudo cp wrk /usr/local/bin
```

#### **wrk Test Commands:**
```bash
# Basic test: 500 requests/minute for 2 minutes
wrk -t12 -c50 -d2m --rate=500 https://coders64.xyz/projects/haatbazar/

# High load test: 5000 requests/minute for 1 minute
wrk -t20 -c100 -d1m --rate=5000 https://coders64.xyz/projects/haatbazar/

# Test with custom script
wrk -t12 -c50 -d2m -s script.lua https://coders64.xyz/projects/haatbazar/
```

#### **Custom Lua Script (script.lua):**
```lua
-- Test multiple endpoints
request = function()
   local paths = {"/", "/product-details.php?id=42", "/index.php?category=5"}
   local path = paths[math.random(#paths)]
   return wrk.format("GET", path)
end

response = function(status, headers, body)
   if status ~= 200 then
      print("Error: " .. status)
   end
end
```

---

### **Method 4: JMeter - GUI-Based Comprehensive Testing**

#### **Installation:**
1. Download from https://jmeter.apache.org/
2. Extract and run `bin/jmeter`

#### **JMeter Test Plan Setup:**
1. **Thread Group Settings:**
   - Number of Threads: 100
   - Ramp-up Period: 60 seconds
   - Loop Count: 50

2. **HTTP Request Samplers:**
   - Server: `coders64.xyz`
   - Path: `/projects/haatbazar/`
   - Method: GET

3. **Add Listeners:**
   - View Results Tree
   - Summary Report
   - Response Time Graph

---

## 🔍 **Comprehensive Testing Workflow**

### **Phase 1: Baseline Testing**
```bash
# 1. Single request test
curl -w "@curl-format.txt" -o /dev/null -s https://coders64.xyz/projects/haatbazar/

# 2. Light load test (50 requests)
ab -n 50 -c 5 https://coders64.xyz/projects/haatbazar/

# 3. Medium load test (500 requests)
ab -n 500 -c 25 https://coders64.xyz/projects/haatbazar/
```

### **Phase 2: Stress Testing**
```bash
# 1. High concurrent users (100 concurrent)
ab -n 1000 -c 100 https://coders64.xyz/projects/haatbazar/

# 2. Sustained load (5000 requests)
ab -n 5000 -c 50 https://coders64.xyz/projects/haatbazar/

# 3. Peak load simulation
wrk -t20 -c200 -d2m --rate=5000 https://coders64.xyz/projects/haatbazar/
```

### **Phase 3: Endpoint-Specific Testing**
```bash
# Test critical pages
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/product-details.php?id=42
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/apis/get-products.php
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/admin/apis/dashboard-stats.php
```

---

## 📈 **Key Metrics to Monitor**

### **Response Time Metrics:**
- **Average Response Time** - Should be < 2 seconds
- **95th Percentile** - Should be < 5 seconds
- **99th Percentile** - Should be < 10 seconds
- **Maximum Response Time** - Identify outliers

### **Throughput Metrics:**
- **Requests per Second (RPS)** - Server capacity
- **Transfer Rate** - Bandwidth utilization
- **Concurrent Users Supported** - Scalability limit

### **Error Metrics:**
- **Error Rate** - Should be < 1%
- **HTTP Status Codes** - Monitor 4xx and 5xx errors
- **Connection Failures** - Network issues
- **Timeouts** - Server overload indicators

---

## 🔧 **Curl Format Template**

Create `curl-format.txt`:
```
     time_namelookup:  %{time_namelookup}s\n
        time_connect:  %{time_connect}s\n
     time_appconnect:  %{time_appconnect}s\n
    time_pretransfer:  %{time_pretransfer}s\n
       time_redirect:  %{time_redirect}s\n
  time_starttransfer:  %{time_starttransfer}s\n
                     ----------\n
          time_total:  %{time_total}s\n
         size_download: %{size_download} bytes\n
         speed_download: %{speed_download} bytes/sec\n
```

---

## 🎯 **Recommended Testing Schedule**

### **Quick Health Check (Daily):**
```bash
ab -n 100 -c 10 https://coders64.xyz/projects/haatbazar/
```

### **Weekly Performance Test:**
```bash
ab -n 1000 -c 50 https://coders64.xyz/projects/haatbazar/
```

### **Monthly Stress Test:**
```bash
artillery run load-test-config.yml
```

---

## 📊 **Expected Results & Benchmarks**

### **Good Performance Indicators:**
- **Response Time**: < 2 seconds average
- **Throughput**: > 100 requests/second
- **Error Rate**: < 0.1%
- **Concurrent Users**: > 100 without degradation

### **Warning Signs:**
- **Response Time**: > 5 seconds average
- **High Error Rate**: > 1%
- **Memory Issues**: Increasing response times
- **Database Bottlenecks**: Slow query responses

---

## 🔍 **Bottleneck Identification**

### **Common Bottlenecks:**
1. **Database Queries** - Slow MySQL queries
2. **PHP Processing** - Inefficient code execution
3. **Image Loading** - Large product images
4. **Session Management** - PHP session handling
5. **Network Latency** - Server location/CDN issues

### **Monitoring Commands:**
```bash
# Monitor server resources during testing
top -p $(pgrep -d',' php)
iostat -x 1
free -h
netstat -i
```

---

## 📝 **Test Results Documentation**

### **Results Template:**
```
Date: [DATE]
Test Duration: [DURATION]
Total Requests: [NUMBER]
Concurrent Users: [NUMBER]

Results:
- Average Response Time: [TIME]ms
- 95th Percentile: [TIME]ms
- Requests per Second: [RPS]
- Error Rate: [PERCENTAGE]%
- Peak Memory Usage: [MB]

Bottlenecks Identified:
- [ISSUE 1]
- [ISSUE 2]

Recommendations:
- [RECOMMENDATION 1]
- [RECOMMENDATION 2]
```

---

## 🚀 **Performance Optimization Tips**

### **Immediate Improvements:**
1. **Enable PHP OPcache**
2. **Optimize Database Queries**
3. **Compress Images**
4. **Enable GZIP Compression**
5. **Use CDN for Static Assets**

### **Advanced Optimizations:**
1. **Implement Redis Caching**
2. **Database Query Optimization**
3. **Load Balancing**
4. **Server-Side Caching**
5. **Minify CSS/JavaScript**

---

## ⚠️ **Testing Precautions**

### **Important Notes:**
- **Start with low load** and gradually increase
- **Monitor server resources** during testing
- **Test during off-peak hours** to avoid affecting real users
- **Have backup/rollback plan** ready
- **Coordinate with hosting provider** for high-load tests

### **Ethical Considerations:**
- **Don't overload shared hosting**
- **Respect server resources**
- **Follow hosting provider guidelines**
- **Monitor and stop if issues occur**

---

## 📞 **Emergency Procedures**

### **If Server Becomes Unresponsive:**
1. **Stop all load tests immediately**
2. **Check server status via hosting panel**
3. **Contact hosting provider if needed**
4. **Monitor error logs**
5. **Implement emergency caching if required**

---

**Happy Load Testing! 🚀**

*Remember: The goal is to improve performance, not break the server!*