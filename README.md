## huaweicloud_ddns_api

基于[华为云API签名 SDK](https://support.huaweicloud.com/devg-apisign/api-sign-sdk-php.html)实现，仅需一个GET请求即可更新DNS解析。

### 使用

自动判断 IPv4/IPv6地址，修改A/AAAA解析。

需提前添加一条A/AAAA解析。

#### 公开版 public.php
> 需在URL中添加参数ak,sk,domain_name
> 
>    https://api.example.com/huaweicloud_ddns_api/public.php?ak=akxxxxx&sk=skxxxxxx&domain_name=test.example.com

#### 私有版 private.php
> 在文件中修改参数ak,sk,domain_name，在URL中添加参数token
> 
>    https://api.example.com/huaweicloud_ddns_api/private.php?token=your_expected_token_value

#### 示例(IPv6)
```bash
wget -q -O /dev/null -6 https://api.example.com/huaweicloud_ddns_api/private.php?token=your_expected_token_value
```

```bash
curl -6 -s -o /dev/null https://api.example.com/huaweicloud_ddns_api/private.php?token=your_expected_token_value
```


