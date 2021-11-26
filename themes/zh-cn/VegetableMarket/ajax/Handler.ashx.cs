using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using Newtonsoft.Json;
using System.Configuration;
using System.IO;
using VegetableMarket.common;
using System.Text.RegularExpressions;
using System.Web.Security;
using System.Web.UI;
using System.Web.SessionState;
using Newtonsoft.Json.Linq;
using System.Data;

namespace VegetableMarket.ajax
{
    /// <summary>
    /// Handler 的摘要说明
    /// </summary>
    public class Handler : IHttpHandler, IRequiresSessionState
    {
        public static int OrderStatus = 0;//订单状态 为1则成功

        public void ProcessRequest(HttpContext context)
        {
            string action = context.Request["action"];
            string result = string.Empty;
            switch (action)
            {
                case "addOrders":
                    result = addOrders(context);
                    break;
                case "addProduct":
                    result = addProduct(context);
                    break;
                case "delProduct":
                    result = delProduct(context);
                    break;
                case "getModel":
                    result = getModel(context);
                    break;
                case "editProduct":
                    result = editProduct(context);
                    break;
                case "editPassword":
                    result = editPassword(context);
                    break;
                case "getPictures":
                    result = getPictures(context);
                    break;
                case "delpic":
                    result = delpic(context);
                    break;
                case "cashOrder":
                    result = cashOrder(context);
                    break;
                case "payOrder":
                    result = payOrder(context);
                    break;
                case "findProduct":
                    result = findProduct(context);
                    break;
                case "delDistribution":
                    result = delDistribution(context);
                    break;
                case "addDistribution":
                    result = addDistribution(context);
                    break;
                case "getDistribution":
                    result = getDistribution(context);
                    break;
                case "editDistribution":
                    result = editDistribution(context);
                    break;
                case "addBanner":
                    result = addBanner(context);
                    break;
                case "editBanner":
                    result = editBanner(context);
                    break;
                case "delBanner":
                    result = delBanner(context);
                    break;
                case "getBanner":
                    result = getBanner(context);
                    break;
                case "editSys":
                    result = editSys(context);
                    break;
                case "setCookie":
                    result = setCookie(context);
                    break;
                case "sxjPro":
                    result = sxj(context);
                    break;
                case "addVolume":
                    result = addVolume(context);
                    break;
                case "getOneSecond":
                    result = getOneSecond(context);
                    break;
                case "editOneSecond":
                    result = editOneSecond(context);
                    break;
                //case "CheckPayment":
                //    result = CheckPayment(context);
                //    break;
                case "ImportProduct":
                    result = ImportProduct(context);
                    break;
                default:
                    break;
            }
            context.Response.ContentType = "text/plain";
            context.Response.Write(result);
        }

        public bool IsTelephone(string sTelephone)
        {
            Regex rx = new Regex(@"^(0|4)[0-9]{8,9}$");
            if (rx.IsMatch(sTelephone))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        private string addOrders(HttpContext context)
        {
            Configuration cfg = System.Web.Configuration.WebConfigurationManager.OpenWebConfiguration(System.Web.HttpContext.Current.Request.ApplicationPath);
            AppSettingsSection ass = cfg.AppSettings;

            string EnNameNotNull = "";
            string EnPhoneNotNull = "";
            string EnPhoneNotFormat = "";
            string EnSingleFailure = "";
            string EnLowStocks = "";
            string EnCreditCardNoNotNull = "";
            string EnCreditCardYearNotNull = "";
            string EnCreditCardMonthNotNull = "";
            string EnCVCNotNull = "";

            //获取中英文标识
            int LanguageType = 0;
            if (HttpContext.Current.Request.Cookies["LanguageType"] != null)
            {
                int.TryParse(HttpContext.Current.Request.Cookies["LanguageType"].Value, out LanguageType);
            }

            if (LanguageType != 0)
            {
                EnNameNotNull = ass.Settings["EnNameNotNull"].Value;
                EnPhoneNotNull = ass.Settings["EnPhoneNotNull"].Value;
                EnPhoneNotFormat = ass.Settings["EnPhoneNotFormat"].Value;
                EnSingleFailure = ass.Settings["EnSingleFailure"].Value;
                EnLowStocks = ass.Settings["EnLowStocks"].Value;
            }
            else
            {
                EnNameNotNull = ass.Settings["NameNotNull"].Value;
                EnPhoneNotNull = ass.Settings["PhoneNotNull"].Value;
                EnPhoneNotFormat = ass.Settings["PhoneNotFormat"].Value;
                EnSingleFailure = ass.Settings["SingleFailure"].Value;
                EnCreditCardNoNotNull = ass.Settings["CreditCardYear"].Value;
                EnCreditCardYearNotNull = ass.Settings["CreditCardYear"].Value;
                EnCreditCardMonthNotNull = ass.Settings["CreditCardMonth"].Value;
                EnCVCNotNull = ass.Settings["CVC"].Value;
            }
            //17位订单ID
            string UserOrderID = DateTime.Now.ToString("yyyyMMddHHmmssfff");
            var OrderQuery = context.Request["OrderQuery"];
            var TotalPrice = context.Request["TotalPrice"];
            string UserName = context.Request["UserName"];
            string UserPhone = context.Request["UserPhone"];
            string UserAddress = context.Request["UserAddress"];
            string PaymentType = context.Request["PaymentType"];//付款方式 0：微信支付  1：paypal   2：信用卡
            string Remarks = context.Request["Remarks"];
            string exsitOrderId = context.Request["ExistOrderId"];

            string Street = context.Request["Street"];
            string Suburb = context.Request["Suburb"];
            string PostCode = context.Request["PostCode"];

            JsonResult result = new JsonResult();
            if (string.IsNullOrEmpty(UserName))
            {
                result.result = false;
                result.message = EnNameNotNull;
                return JsonConvert.SerializeObject(result);
            }
            if (string.IsNullOrEmpty(UserPhone))
            {
                result.result = false;
                result.message = EnPhoneNotNull;
                return JsonConvert.SerializeObject(result);
            }
            if (!IsTelephone(UserPhone.ToString()))//验证有效电话
            {
                result.result = false;
                result.message = EnPhoneNotFormat;
                return JsonConvert.SerializeObject(result);
            }

            if (!new GrouponDAL.DeliverablePostCode_table().CheckIsDeliverable(PostCode))
            {
                result.result = false;
                result.message = "非常抱歉，您的地址超出可配送范围";
                return JsonConvert.SerializeObject(result);
            }

            GrouponDAL.userOrders dal = new GrouponDAL.userOrders();
            try
            {
                GrouponDAL.product dal_product = new GrouponDAL.product();
                GrouponModel.product model = new GrouponModel.product();
                List<GrouponModel.product> productList = dal_product.GetList(LanguageType);
                #region
                int row = dal.GetListCountByExsitOrderId(exsitOrderId, 0);
                if (row == 0)
                {
                    JArray orderDic = JsonConvert.DeserializeObject<JArray>(OrderQuery);
                    foreach (var order in orderDic)
                    {
                        var productId = order["productId"];
                        var BuyCount = Convert.ToInt16(order["quantity"]);
                        model = productList.Where(x => x.ProductID == Convert.ToInt32(productId)).First();
                        //验证库存
                        //if (model.ProductQuantity < BuyCount)
                        //{
                        //    result.result = false;
                        //    result.message = EnLowStocks + " Name:" + model.ProductName;
                        //    return JsonConvert.SerializeObject(result);
                        //}
                        GrouponModel.userOrders orderModel = new GrouponModel.userOrders()
                        {
                            UserOrderID = UserOrderID,
                            ProductID = productId.ToString(),
                            UserName = UserName,
                            UserPhone = UserPhone,
                            PaymentType = Convert.ToInt16(PaymentType),
                            Quantity = BuyCount,
                            UserAddress = UserAddress,
                            Remarks = Remarks,
                            StreetAddress = Street,
                            Suburb = Suburb,
                            PostCode = PostCode
                        };
                        row += dal.AddOrder(orderModel);
                    }
                }
                else
                {
                    dal.UpdateOrderId(exsitOrderId, UserOrderID);
                }

                if (row > 0)
                {
                    //string onlinePrice = ((double)model.OnlinePaymentRatio / (double)100 * (double)model.NowPrice * (double)BuyCount).ToString("#0.00");
                    string sUrl = new PaymentClass().Payment(UserOrderID, PaymentType, TotalPrice, context.Request.Url.Scheme + "://" + context.Request.Url.Authority, "");
                    result.result = true;
                    result.message = sUrl;
                    if (sUrl == "false")
                    {
                        result.result = false;
                        result.message = "请输入正确信用卡信息！";
                    }

                    //Response.Redirect(sUrl);

                    HttpCookie UserNameCookie = new HttpCookie("UserName");
                    UserNameCookie.Value = UserName;
                    context.Response.AppendCookie(UserNameCookie);

                    HttpCookie UserPhoneCookie = new HttpCookie("UserPhone");
                    UserPhoneCookie.Value = UserPhone;
                    context.Response.AppendCookie(UserPhoneCookie);

                    return JsonConvert.SerializeObject(result);
                }
                else
                {
                    result.result = true;
                    result.message = EnSingleFailure;
                    return JsonConvert.SerializeObject(result);
                }
            }
            catch (Exception ex)
            {
                result.result = true;
                result.message = EnSingleFailure;
                return JsonConvert.SerializeObject(ex.ToString());
            }
                #endregion
        }

        private string addProduct(HttpContext context)
        {
            string RestaurantName = context.Request["RestaurantName"];
            string RestaurantAddress = context.Request["RestaurantAddress"];
            int BusinessCircle = context.Request["BusinessCircle"] == null ? 0 : int.Parse(context.Request["BusinessCircle"]);
            int SortNum = context.Request["SortNum"] == null ? 0 : int.Parse(context.Request["SortNum"]);
            int LanguageType = context.Request["LanguageType"] == null ? 0 : int.Parse(context.Request["LanguageType"]);
            string RestaurantPhone = context.Request["RestaurantPhone"];
            string BossPhone = context.Request["BossPhone"];
            string ProductName = context.Request["ProductName"];
            string CurrentPopularity = context.Request["CurrentPopularity"];
            string OnlinePaymentRatio = context.Request["OnlinePaymentRatio"];
            decimal OriginalPrice = context.Request["OriginalPrice"] == null ? 0 : decimal.Parse(context.Request["OriginalPrice"]);
            decimal NowPrice = context.Request["NowPrice"] == null ? 0 : decimal.Parse(context.Request["NowPrice"]);
            int CurrentQuantity = context.Request["CurrentQuantity"] == null ? 0 : int.Parse(context.Request["CurrentQuantity"]);
            int ProductQuantity = context.Request["ProductQuantity"] == null ? 0 : int.Parse(context.Request["ProductQuantity"]);
            int ProductType = context.Request["ProductType"] == null ? 0 : int.Parse(context.Request["ProductType"]);
            string Details = Microsoft.JScript.GlobalObject.unescape(context.Request["Details"]);
            string IsSendMessageBoss = context.Request["IsSendMessageBoss"];
            string IsSendMessageUser = context.Request["IsSendMessageUser"];

            int Recommend = context.Request["Recommend"] == null ? 0 : int.Parse(context.Request["Recommend"]);
            int mealsId = context.Request["mealsId"] == null ? 0 : int.Parse(context.Request["mealsId"]);
            //在套餐表里增加小时字段，填写内容比如：24小时内使用、48小时内使用。
            int ProductTimeliness = context.Request["ProductTimeliness"] == null ? 0 : int.Parse(context.Request["ProductTimeliness"]);
            string ChiType = context.Request["ChiType"];
            string IsBuyMore = context.Request["IsBuyMore"];
            string ProductLogo = "";
            HttpPostedFile file = context.Request.Files["ProductLogo"];
            if (file != null)
            {
                //ProductLogo = file.FileName;
                string fileName = file.FileName;
                string fileSuffix = fileName.Substring(fileName.LastIndexOf(".") + 1).ToLower();
                fileName = DateTime.Now.ToString("yyyMMddHHmmssfff") + System.Guid.NewGuid().ToString("N").Substring(0, 8) + "." + fileSuffix;
                string year = DateTime.Now.ToString("yyyy");
                string month = DateTime.Now.ToString("MM");
                string day = DateTime.Now.ToString("dd");
                if (month.Substring(0, 1) == "0")
                {
                    month = month.Substring(1);
                }
                if (day.Substring(0, 1) == "0")
                {
                    day = day.Substring(1);
                }
                if (!Directory.Exists(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/")))
                {
                    Directory.CreateDirectory(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/"));
                }
                file.SaveAs(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName));
                ProductLogo = "/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName;
            }

            GrouponModel.product model = new GrouponModel.product();
            model.SortNum = SortNum;
            model.RestaurantName = RestaurantName;
            model.RestaurantAddress = RestaurantAddress;
            model.BusinessCircle = BusinessCircle;
            model.RestaurantPhone = RestaurantPhone;
            model.BossPhone = BossPhone;
            model.ProductName = ProductName;
            model.OriginalPrice = OriginalPrice;
            model.NowPrice = NowPrice;
            model.CurrentQuantity = CurrentQuantity;
            model.ProductQuantity = ProductQuantity;
            model.Details = Details;
            model.ProductLogo = ProductLogo;
            model.ProductType = ProductType;
            int iCurrentPopularity = 0; double iOnlinePaymentRatio = 0;
            int.TryParse(CurrentPopularity, out iCurrentPopularity);
            double.TryParse(OnlinePaymentRatio, out iOnlinePaymentRatio);
            model.CurrentPopularity = iCurrentPopularity;
            model.OnlinePaymentRatio = iOnlinePaymentRatio;
            model.IsSendMessageBoss = IsSendMessageBoss;
            model.IsSendMessageUser = IsSendMessageUser;
            model.LanguageType = LanguageType;
            model.Recommend = Recommend;
            model.mealsId = mealsId;
            //在套餐表里增加小时字段，填写内容比如：24小时内使用、48小时内使用。
            model.ProductTimeliness = ProductTimeliness;
            model.ChiType = Convert.ToInt32(ChiType);//0：团购 1：展示  2：推荐
            model.IsBuyMore = Convert.ToInt32(IsBuyMore);//0：不限购  1：限购
            JsonResult result = new JsonResult();
            GrouponDAL.product dal = new GrouponDAL.product();
            if (dal.add(model) > 0)
            {
                result.result = true;
                result.message = "添加成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "添加失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string delProduct(HttpContext context)
        {
            string ProductID = context.Request["ProductID"];
            JsonResult result = new JsonResult();
            GrouponDAL.product dal = new GrouponDAL.product();

            if (dal.Delete(ProductID))
            {
                result.result = true;
                result.message = "删除成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "删除失败";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string delDistribution(HttpContext context)
        {
            string DistributionID = !string.IsNullOrEmpty(context.Request["DistributionID"]) ? context.Request["DistributionID"] : "0";//分销ID
            JsonResult result = new JsonResult();
            GrouponDAL.DistributionInfoTable dal = new GrouponDAL.DistributionInfoTable();

            if (dal.del(int.Parse(DistributionID)))
            {
                result.result = true;
                result.message = "删除成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "删除失败";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string getModel(HttpContext context)
        {
            JsonResult result = new JsonResult();
            string ProductID = !string.IsNullOrEmpty(context.Request["ProductID"]) ? context.Request["ProductID"] : "0";//获得产品ID
            if (ProductID == "0")
            {
                result.result = false;
                result.message = "不存在该产品,请联系客服";
                return JsonConvert.SerializeObject(result);
            }

            GrouponModel.product model = new GrouponModel.product();
            GrouponDAL.product dal = new GrouponDAL.product();
            model = dal.GetModel(ProductID);
            if (model != null)
            {
                result.result = true;
                result.message = "获取成功";
                result.data = JsonConvert.SerializeObject(model);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "获取失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string editProduct(HttpContext context)
        {
            string ProductID = context.Request["ProductID"];
            string RestaurantName = context.Request["RestaurantName"];
            string RestaurantAddress = context.Request["RestaurantAddress"];
            int BusinessCircle = context.Request["BusinessCircle"] == null ? 0 : int.Parse(context.Request["BusinessCircle"]);
            int SortNum = context.Request["SortNum"] == null ? 0 : int.Parse(context.Request["SortNum"]);
            int LanguageType = context.Request["LanguageType"] == null ? 0 : int.Parse(context.Request["LanguageType"]);
            string RestaurantPhone = context.Request["RestaurantPhone"];
            string BossPhone = context.Request["BossPhone"];
            string ProductName = context.Request["ProductName"];
            string CurrentPopularity = context.Request["CurrentPopularity"];
            string OnlinePaymentRatio = context.Request["OnlinePaymentRatio"];
            decimal OriginalPrice = context.Request["OriginalPrice"] == null ? 0 : decimal.Parse(context.Request["OriginalPrice"]);
            decimal NowPrice = context.Request["NowPrice"] == null ? 0 : decimal.Parse(context.Request["NowPrice"]);
            int CurrentQuantity = context.Request["CurrentQuantity"] == null ? 0 : int.Parse(context.Request["CurrentQuantity"]);
            int ProductQuantity = context.Request["ProductQuantity"] == null ? 0 : int.Parse(context.Request["ProductQuantity"]);
            int ProductType = context.Request["ProductType"] == null ? 0 : int.Parse(context.Request["ProductType"]);
            string Details = Microsoft.JScript.GlobalObject.unescape(context.Request["Details"]);
            string IsSendMessageBoss = context.Request["IsSendMessageBoss"];
            string IsSendMessageUser = context.Request["IsSendMessageUser"];
            string ProductLogo = "";
            int Recommend = context.Request["Recommend"] == null ? 0 : int.Parse(context.Request["Recommend"]);
            int mealsId = context.Request["mealsId"] == null ? 0 : int.Parse(context.Request["mealsId"]);
            //在套餐表里增加小时字段，填写内容比如：24小时内使用、48小时内使用。
            int ProductTimeliness = context.Request["ProductTimeliness"] == null ? 0 : int.Parse(context.Request["ProductTimeliness"]);
            string ChiType = context.Request["ChiType"];
            string IsBuyMore = context.Request["IsBuyMore"];
            GrouponDAL.product dal = new GrouponDAL.product();
            GrouponModel.product model = dal.GetModel(ProductID);
            model.RestaurantName = RestaurantName;
            model.RestaurantAddress = RestaurantAddress;
            model.BusinessCircle = BusinessCircle;
            model.RestaurantPhone = RestaurantPhone;
            model.BossPhone = BossPhone;
            model.ProductName = ProductName;
            model.OriginalPrice = OriginalPrice;
            model.NowPrice = NowPrice;
            model.CurrentQuantity = CurrentQuantity;
            model.ProductQuantity = ProductQuantity;
            model.Details = Details;
            model.CurrentPopularity = Convert.ToInt32(CurrentPopularity);
            model.OnlinePaymentRatio = Convert.ToDouble(OnlinePaymentRatio);
            model.IsSendMessageBoss = IsSendMessageBoss;
            model.IsSendMessageUser = IsSendMessageUser;
            model.SortNum = SortNum;
            model.LanguageType = LanguageType;
            model.ProductType = ProductType;
            model.Recommend = Recommend;
            model.mealsId = mealsId;
            model.ProductTimeliness = ProductTimeliness;
            model.ChiType = Convert.ToInt32(ChiType);
            model.IsBuyMore = Convert.ToInt32(IsBuyMore);

            HttpPostedFile file = context.Request.Files["ProductLogo"];
            if (file != null)
            {
                //ProductLogo = file.FileName;
                string fileName = file.FileName;
                string fileSuffix = fileName.Substring(fileName.LastIndexOf(".") + 1).ToLower();
                fileName = DateTime.Now.ToString("yyyMMddHHmmssfff") + System.Guid.NewGuid().ToString("N").Substring(0, 8) + "." + fileSuffix;
                string year = DateTime.Now.ToString("yyyy");
                string month = DateTime.Now.ToString("MM");
                string day = DateTime.Now.ToString("dd");
                if (month.Substring(0, 1) == "0")
                {
                    month = month.Substring(1);
                }
                if (day.Substring(0, 1) == "0")
                {
                    day = day.Substring(1);
                }
                if (!Directory.Exists(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/")))
                {
                    Directory.CreateDirectory(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/"));
                }
                file.SaveAs(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName));
                ProductLogo = "/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName;
                model.ProductLogo = ProductLogo;
            }


            JsonResult result = new JsonResult();

            if (dal.edit(model))
            {
                result.result = true;
                result.message = "修改成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "修改失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string editPassword(HttpContext context)
        {
            string oldPassword = context.Request["oldPassword"];
            string newPassword1 = context.Request["newPassword1"];
            string newPassword2 = context.Request["newPassword2"];
            JsonResult result = new JsonResult();

            if (oldPassword == "")
            {
                result.result = false;
                result.message = "请填写旧密码";
                return JsonConvert.SerializeObject(result);
            }
            if (newPassword1 == "")
            {
                result.result = false;
                result.message = "请填写新密码";
                return JsonConvert.SerializeObject(result);
            }
            if (newPassword2 == "")
            {
                result.result = false;
                result.message = "请确认新密码";
                return JsonConvert.SerializeObject(result);
            }
            if (newPassword1 != newPassword2)
            {
                result.result = false;
                result.message = "新密码填写不一致";
                return JsonConvert.SerializeObject(result);
            }

            else
            {
                string admin = HttpContext.Current.Request.Cookies["admuser"].Value;
                GrouponModel.SysUser user = new GrouponDAL.SysUser().GetInfo(admin, FormsAuthentication.HashPasswordForStoringInConfigFile(oldPassword, "MD5").ToLower());
                if (user != null && user.UserId > 0)
                {

                    GrouponModel.SysUser pojo = new GrouponModel.SysUser();
                    pojo.UserId = user.UserId;
                    pojo.Psd = FormsAuthentication.HashPasswordForStoringInConfigFile(newPassword2, "MD5").ToLower();
                    pojo.Account = user.Account;
                    new GrouponDAL.SysUser().Update(pojo);
                    result.result = true;
                    result.message = "修改成功";
                    return JsonConvert.SerializeObject(result);
                }
                else if (!string.IsNullOrEmpty(admin))
                {
                    result.result = false;
                    result.message = "旧密码不正确！";
                    return JsonConvert.SerializeObject(result);
                }
                else
                {
                    result.result = false;
                    result.message = "登陆超时，请重新登陆！";
                    result.flage = -1;
                    return JsonConvert.SerializeObject(result);
                }

            }
        }

        private string getPictures(HttpContext context)
        {
            string ProductID = !string.IsNullOrEmpty(context.Request["ProductID"]) ? context.Request["ProductID"] : "0";//获得产品ID
            List<GrouponModel.product_img> list = new List<GrouponModel.product_img>();
            JsonResult result = new JsonResult();
            GrouponDAL.product_img dal = new GrouponDAL.product_img();
            list = dal.GetList(ProductID);
            if (list.Count > 0)
            {
                result.result = true;
                result.message = "获取成功";
                result.data = JsonConvert.SerializeObject(list);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "无图片";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string delpic(HttpContext context)
        {
            string ImageID = context.Request["ImageID"];
            JsonResult result = new JsonResult();
            GrouponDAL.product_img dal = new GrouponDAL.product_img();

            if (dal.Delete(int.Parse(ImageID)))
            {
                result.result = true;
                result.message = ImageID;
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "删除失败";
                return JsonConvert.SerializeObject(result);
            }

        }


        /// <summary>
        /// 置成付款状态
        /// </summary>
        /// <param name="context"></param>
        /// <param name="state">0：不发短信   1：发送短信</param>
        /// <returns></returns>
        private string payOrder(HttpContext context)
        {
            string UserOrderID = context.Request["UserOrderID"].ToString();
            string ProductID = context.Request["ProductID"].ToString();
            string state = context.Request["state"].ToString(); //前端接收的状态值 1:付款   2：短信   3：退款
            JsonResult result = new JsonResult();
            GrouponDAL.userOrders dal = new GrouponDAL.userOrders();
            GrouponModel.userOrders model = new GrouponModel.userOrders();

            if (UserOrderID == "" || UserOrderID == null)
            {
                result.result = false;
                result.message = "订单号不正确，付款失败";
                return JsonConvert.SerializeObject(result);
            }

            model = dal.GetModel(UserOrderID, Convert.ToInt32(ProductID));
            switch (state)
            {
                case "1":       //只修改订单状态
                    if (model.OrderStatus == 2)
                    {
                        if (new GrouponDAL.userOrders().UpdatePaymentStatus(UserOrderID, ProductID, "1"))
                        {
                            result.result = true;
                            result.message = "付款成功";
                        }
                        else
                        {
                            result.result = true;
                            result.message = "付款失败";
                        }
                    }
                    else
                    {
                        result.result = false;
                        result.message = "只有【兑付】的订单状态才可以付款哦！";
                    }
                    break;
                case "2":
                    if (model.OrderStatus == 0)
                    {
                        new NotifyClass().OrderSuccessNotify(UserOrderID);//付款成功后,更新状态+短信通知
                        result.result = true;
                        result.message = "付款成功";
                    }
                    else
                    {
                        result.result = false;
                        result.message = "只有【未付款】的订单状态才可以短信推送哦！";
                    }
                    break;
                case "3":   //退款
                    if (model.OrderStatus == 1)
                    {
                        if (new GrouponDAL.userOrders().UpdatePaymentStatus(UserOrderID, ProductID, "3"))
                        {
                            result.result = true;
                            result.message = "退款成功";
                        }
                        else
                        {
                            result.result = true;
                            result.message = "退款失败";
                        }
                    }
                    else
                    {
                        result.result = false;
                        result.message = "只有【已付款】的订单状态才可以退款哦！";
                    }
                    break;
                default:
                    break;
            }
            return JsonConvert.SerializeObject(result);
        }

        private string cashOrder(HttpContext context)
        {
            string UserOrderID = context.Request["UserOrderID"].ToString();
            string ProductID = context.Request["ProductID"].ToString();
            JsonResult result = new JsonResult();
            GrouponDAL.userOrders dal = new GrouponDAL.userOrders();
            GrouponModel.userOrders model = new GrouponModel.userOrders();

            if (UserOrderID == "" || UserOrderID == null)
            {
                result.result = false;
                result.message = "订单号不正确，兑付失败";
                return JsonConvert.SerializeObject(result);
            }

            model = dal.GetModel(UserOrderID, Convert.ToInt32(ProductID));

            if (model.OrderStatus != 1)
            {
                result.result = false;
                result.message = "订单状态不正确，兑付失败";
                return JsonConvert.SerializeObject(result);
            }


            if (dal.OrderCash(UserOrderID, ProductID))
            {
                result.result = true;
                result.message = "兑付成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "兑付失败";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string addDistribution(HttpContext context)
        {
            string ProductID = !string.IsNullOrEmpty(context.Request["ProductID"]) ? context.Request["ProductID"] : "0";
            string DistributionType = !string.IsNullOrEmpty(context.Request["DistributionType"]) ? context.Request["DistributionType"] : "0";//分销类别 0：按照产品分销 1：按照商城分销
            string SalespersonName = context.Request["SalespersonName"];
            string SalespersonPhone = context.Request["SalespersonPhone"];
            int Proportion = context.Request["Proportion"] == null ? 0 : int.Parse(context.Request["Proportion"]);
            JsonResult result = new JsonResult();
            if (SalespersonName == "")
            {
                result.result = false;
                result.message = "请输入分销人名称";
                return JsonConvert.SerializeObject(result);
            }
            if (string.IsNullOrEmpty(SalespersonPhone))
            {
                result.result = false;
                result.message = "请输入有效电话";
                return JsonConvert.SerializeObject(result);
            }

            if (DistributionType == "1")
            {
                ProductID = "99999";
            }

            GrouponModel.DistributionInfoTable pojo = new GrouponModel.DistributionInfoTable();
            try
            {
                pojo.DistributionType = DistributionType;
                pojo.SalespersonName = SalespersonName;
                pojo.SalespersonPhone = SalespersonPhone;
                pojo.ProductID = ProductID;
                pojo.IsDelete = 0;
                pojo.Proportion = Proportion;
                if (DistributionType == "0")
                    pojo.DistributionURL = @"http://test.ausgroupon.com/details.aspx?ProductID=" + ProductID;
                else
                    pojo.DistributionURL = @"http://test.ausgroupon.com";
                if (new GrouponDAL.DistributionInfoTable().AddDistribution(pojo) > 0)
                {
                    result.result = true;
                    result.message = "增加成功";
                    return JsonConvert.SerializeObject(result);
                }
                else
                {
                    result.result = true;
                    result.message = "增加失败";
                    return JsonConvert.SerializeObject(result);
                }

            }
            catch (Exception ex)
            {
                new GrouponDAL.product().AddLog("addDistribution:" + ex.Message);
                result.result = true;
                result.message = "增加失败";
                return JsonConvert.SerializeObject(ex.ToString());
            }
        }

        private string getOneSecond(HttpContext context)
        {
            string OneSecondID = !string.IsNullOrEmpty(context.Request["OneSecondID"]) ? context.Request["OneSecondID"] : "0";//分销ID
            JsonResult result = new JsonResult();
            if (OneSecondID == "0")
            {
                result.result = false;
                result.message = "获取ID失败,请联系客服";
                return JsonConvert.SerializeObject(result);
            }

            GrouponModel.OneSecond_Table model = new GrouponModel.OneSecond_Table();
            GrouponDAL.OneSecond dal = new GrouponDAL.OneSecond();
            model = dal.GetModel()[0];
            if (model != null)
            {
                result.result = true;
                result.message = "获取成功";
                result.data = JsonConvert.SerializeObject(model);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "获取失败";
                return JsonConvert.SerializeObject(result);
            }
        }
        private string getDistribution(HttpContext context)
        {
            string DistributionID = !string.IsNullOrEmpty(context.Request["DistributionID"]) ? context.Request["DistributionID"] : "0";//分销ID
            JsonResult result = new JsonResult();
            if (DistributionID == "0")
            {
                result.result = false;
                result.message = "获取分销ID失败,请联系客服";
                return JsonConvert.SerializeObject(result);
            }

            GrouponModel.DistributionInfoTable model = new GrouponModel.DistributionInfoTable();
            GrouponDAL.DistributionInfoTable dal = new GrouponDAL.DistributionInfoTable();
            model = dal.GetModel(DistributionID);
            if (model != null)
            {
                result.result = true;
                result.message = "获取成功";
                result.data = JsonConvert.SerializeObject(model);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "获取失败";
                return JsonConvert.SerializeObject(result);
            }
        }



        private string editOneSecond(HttpContext context)
        {
            string OneSecondID = context.Request["OneSecondID"];
            string Title1 = context.Request["Title1"];
            string Title2 = context.Request["Title2"];
            string Title3 = context.Request["Title3"];
            string URL = context.Request["URL"];
            string Deadline = context.Request["Deadline"];
            string ProductID = context.Request["ProductID"];

            JsonResult result = new JsonResult();

            GrouponModel.OneSecond_Table pojo = new GrouponModel.OneSecond_Table();
            try
            {
                pojo.OneSecondID = OneSecondID;
                pojo.Title1 = Title1;
                pojo.Title2 = Title2;
                pojo.Title3 = Title3;
                pojo.URL = URL;
                pojo.Deadline = Deadline;
                pojo.ProductID = ProductID;

                if (new GrouponDAL.OneSecond().UpdateOneSecond(pojo) > 0)
                {
                    result.result = true;
                    result.message = "修改成功";
                    return JsonConvert.SerializeObject(result);
                }
                else
                {
                    result.result = true;
                    result.message = "修改失败";
                    return JsonConvert.SerializeObject(result);
                }

            }
            catch (Exception ex)
            {
                new GrouponDAL.product().AddLog("OneSecond_Table:" + ex.Message);
                result.result = true;
                result.message = "修改失败";
                return JsonConvert.SerializeObject(ex.ToString());
            }
        }
        private string editDistribution(HttpContext context)
        {
            string DistributionType = !string.IsNullOrEmpty(context.Request["DistributionType"]) ? context.Request["DistributionType"] : "0";//分销类别 0：按照产品分销 1：按照商城分销
            string DistributionID = !string.IsNullOrEmpty(context.Request["Us"]) ? context.Request["Us"] : "0";//分销ID
            string ProductID = context.Request["ProductID"];
            string SalespersonName = context.Request["SalespersonName"];
            string SalespersonPhone = context.Request["SalespersonPhone"];
            int Proportion = context.Request["Proportion"] == null ? 0 : int.Parse(context.Request["Proportion"]);
            if (DistributionType == "1")
            {
                ProductID = "99999";
            }
            JsonResult result = new JsonResult();
            if (SalespersonName == "")
            {
                result.result = false;
                result.message = "请输入分销人名称";
                return JsonConvert.SerializeObject(result);
            }
            if (string.IsNullOrEmpty(SalespersonPhone))
            {
                result.result = false;
                result.message = "请输入有效电话";
                return JsonConvert.SerializeObject(result);
            }
            GrouponModel.DistributionInfoTable pojo = new GrouponModel.DistributionInfoTable();
            try
            {
                pojo.DistributionID = DistributionID;
                pojo.SalespersonName = SalespersonName;
                pojo.SalespersonPhone = SalespersonPhone;
                pojo.ProductID = ProductID;
                pojo.Proportion = Proportion;
                pojo.DistributionType = DistributionType;
                if (DistributionType == "0")
                    pojo.DistributionURL = @"http://test.ausgroupon.com/details.aspx?ProductID=" + ProductID + "&Us=" + DistributionID;
                else
                    pojo.DistributionURL = @"http://test.ausgroupon.com?Us=" + DistributionID;
                if (new GrouponDAL.DistributionInfoTable().UpdateDistribution(pojo) > 0)
                {
                    result.result = true;
                    result.message = "修改成功";
                    return JsonConvert.SerializeObject(result);
                }
                else
                {
                    result.result = true;
                    result.message = "修改失败";
                    return JsonConvert.SerializeObject(result);
                }

            }
            catch (Exception ex)
            {
                new GrouponDAL.product().AddLog("editDistribution:" + ex.Message);
                result.result = true;
                result.message = "修改失败";
                return JsonConvert.SerializeObject(ex.ToString());
            }
        }

        private string addBanner(HttpContext context)
        {
            string BannerName = context.Request["BannerName"];
            string BannerURL = context.Request["BannerURL"];
            int SortNum = context.Request["SortNum"] == null ? 0 : int.Parse(context.Request["SortNum"]);
            int LanguageType = context.Request["LanguageType"] == null ? 0 : int.Parse(context.Request["LanguageType"]);
            string BannerPath = "";
            HttpPostedFile file = context.Request.Files["BannerPath"];
            if (file != null)
            {
                string fileName = file.FileName;
                string fileSuffix = fileName.Substring(fileName.LastIndexOf(".") + 1).ToLower();
                fileName = DateTime.Now.ToString("yyyMMddHHmmssfff") + System.Guid.NewGuid().ToString("N").Substring(0, 8) + "." + fileSuffix;
                string year = DateTime.Now.ToString("yyyy");
                string month = DateTime.Now.ToString("MM");
                string day = DateTime.Now.ToString("dd");
                if (month.Substring(0, 1) == "0")
                {
                    month = month.Substring(1);
                }
                if (day.Substring(0, 1) == "0")
                {
                    day = day.Substring(1);
                }
                if (!Directory.Exists(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/")))
                {
                    Directory.CreateDirectory(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/"));
                }
                file.SaveAs(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName));
                BannerPath = "/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName;
            }

            GrouponModel.BannerInfoTable model = new GrouponModel.BannerInfoTable();
            model.BannerName = BannerName;
            model.BannerURL = BannerURL;
            model.SortNum = SortNum;
            model.BannerPath = BannerPath;
            model.LanguageType = LanguageType;
            JsonResult result = new JsonResult();
            GrouponDAL.BannerInfoTable dal = new GrouponDAL.BannerInfoTable();
            if (dal.Add(model) > 0)
            {
                result.result = true;
                result.message = "添加成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "添加失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string addVolume(HttpContext context)
        {
            string IP = context.Request["IP"];
            string Country = context.Request["Country"];
            string ProductID = context.Request["ProductID"];

            GrouponModel.VistUser_table model = new GrouponModel.VistUser_table();
            model.IP = IP;
            model.Country = Country;
            model.ProductID = ProductID;
            JsonResult result = new JsonResult();
            GrouponDAL.VistUser_table dal = new GrouponDAL.VistUser_table();
            if (dal.IsRepeatIP(IP, ProductID))
            {
                return "";
            }
            if (dal.Add(model) > 0)
            {
                dal.Update(Convert.ToInt32(ProductID));
                result.result = true;
                result.message = "添加成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "添加失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string delBanner(HttpContext context)
        {
            string BannerID = context.Request["BannerID"];
            JsonResult result = new JsonResult();
            GrouponDAL.BannerInfoTable dal = new GrouponDAL.BannerInfoTable();

            if (dal.del(int.Parse(BannerID)))
            {
                result.result = true;
                result.message = "删除成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "删除失败";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string sxj(HttpContext context)
        {
            string id = context.Request["id"];
            string flage = context.Request["flage"];
            JsonResult result = new JsonResult();
            GrouponDAL.product dal = new GrouponDAL.product();

            if (dal.SXJ(int.Parse(id), int.Parse(flage)))
            {
                result.result = true;
                result.message = "操作成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "操作失败";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string editBanner(HttpContext context)
        {
            string BannerID = context.Request["BannerID"];
            string BannerName = context.Request["BannerName"];
            string BannerURL = context.Request["BannerURL"];
            int SortNum = context.Request["SortNum"] == null ? 0 : int.Parse(context.Request["SortNum"]);
            int LanguageType = context.Request["LanguageType"] == null ? 0 : int.Parse(context.Request["LanguageType"]);
            string BannerPath = "";

            GrouponModel.BannerInfoTable model = new GrouponModel.BannerInfoTable();
            model.BannerName = BannerName;
            model.BannerURL = BannerURL;
            model.SortNum = SortNum;
            model.BannerID = Convert.ToInt32(BannerID);
            model.LanguageType = LanguageType;
            HttpPostedFile file = context.Request.Files["BannerPath"];
            if (file != null)
            {
                string fileName = file.FileName;
                string fileSuffix = fileName.Substring(fileName.LastIndexOf(".") + 1).ToLower();
                fileName = DateTime.Now.ToString("yyyMMddHHmmssfff") + System.Guid.NewGuid().ToString("N").Substring(0, 8) + "." + fileSuffix;
                string year = DateTime.Now.ToString("yyyy");
                string month = DateTime.Now.ToString("MM");
                string day = DateTime.Now.ToString("dd");
                if (month.Substring(0, 1) == "0")
                {
                    month = month.Substring(1);
                }
                if (day.Substring(0, 1) == "0")
                {
                    day = day.Substring(1);
                }
                if (!Directory.Exists(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/")))
                {
                    Directory.CreateDirectory(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/"));
                }
                file.SaveAs(HttpContext.Current.Server.MapPath("~/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName));
                BannerPath = "/UpLoad/" + year + "/" + month + "/" + day + "/" + fileName;
                model.BannerPath = BannerPath;
            }

            GrouponDAL.BannerInfoTable dal = new GrouponDAL.BannerInfoTable();
            JsonResult result = new JsonResult();

            if (dal.Update(model) > 0)
            {
                result.result = true;
                result.message = "修改成功";
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "修改失败";
                return JsonConvert.SerializeObject(result);
            }
        }

        private string getBanner(HttpContext context)
        {
            int BannerID = context.Request["BannerID"] == null ? 0 : int.Parse(context.Request["BannerID"]);
            JsonResult result = new JsonResult();
            GrouponDAL.BannerInfoTable dal = new GrouponDAL.BannerInfoTable();

            GrouponModel.BannerInfoTable info = dal.GetInfo(BannerID);
            if (info != null)
            {
                result.result = true;
                result.message = "";
                result.data = Newtonsoft.Json.JsonConvert.SerializeObject(info);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = "信息不存在或已被删除！";
                return JsonConvert.SerializeObject(result);
            }

        }

        private string editSys(HttpContext context)
        {
            JsonResult result = new JsonResult();
            try
            {

                Configuration cfg = System.Web.Configuration.WebConfigurationManager.OpenWebConfiguration(System.Web.HttpContext.Current.Request.ApplicationPath);
                AppSettingsSection ass = cfg.AppSettings;

                string EnIndexSerach = context.Request["EnIndexSerach"];
                ass.Settings["EnIndexSerach"].Value = EnIndexSerach;
                string EnCurrentPopularity = context.Request["EnCurrentPopularity"];
                ass.Settings["EnCurrentPopularity"].Value = EnCurrentPopularity;
                string EnGroupPurchase = context.Request["EnGroupPurchase"];
                ass.Settings["EnGroupPurchase"].Value = EnGroupPurchase;
                string EnIndexTitle = context.Request["EnIndexTitle"];
                ass.Settings["EnIndexTitle"].Value = EnIndexTitle;
                string EnDetailTitle = context.Request["EnDetailTitle"];
                ass.Settings["EnDetailTitle"].Value = EnDetailTitle;
                string EnSalesVolume = context.Request["EnSalesVolume"];
                ass.Settings["EnSalesVolume"].Value = EnSalesVolume;
                string EnMerchant = context.Request["EnMerchant"];
                ass.Settings["EnMerchant"].Value = EnMerchant;
                string EnOnlinePayment = context.Request["EnOnlinePayment"];
                ass.Settings["EnOnlinePayment"].Value = EnOnlinePayment;
                string EnPaymentToTheStore = context.Request["EnPaymentToTheStore"];
                ass.Settings["EnPaymentToTheStore"].Value = EnPaymentToTheStore;
                string EnOriginalPrice = context.Request["EnOriginalPrice"];
                ass.Settings["EnOriginalPrice"].Value = EnOriginalPrice;
                string EnPresentPrice = context.Request["EnPresentPrice"];
                ass.Settings["EnPresentPrice"].Value = EnPresentPrice;
                string EnDiscount = context.Request["EnDiscount"];
                ass.Settings["EnDiscount"].Value = EnDiscount;
                string EnRushToBuy = context.Request["EnRushToBuy"];
                ass.Settings["EnRushToBuy"].Value = EnRushToBuy;
                string EnOrderTitle = context.Request["EnOrderTitle"];
                ass.Settings["EnOrderTitle"].Value = EnOrderTitle;
                string EnNumber = context.Request["EnNumber"];
                ass.Settings["EnNumber"].Value = EnNumber;
                string EnName = context.Request["EnName"];
                ass.Settings["EnName"].Value = EnName;
                string EnNameMsg = context.Request["EnNameMsg"];
                ass.Settings["EnNameMsg"].Value = EnNameMsg;
                string EnPhone = context.Request["EnPhone"];
                ass.Settings["EnPhone"].Value = EnPhone;
                string EnPhoneMsg = context.Request["EnPhoneMsg"];
                ass.Settings["EnPhoneMsg"].Value = EnPhoneMsg;
                string EnPaymentToTheStoreTitle = context.Request["EnPaymentToTheStoreTitle"];
                ass.Settings["EnPaymentToTheStoreTitle"].Value = EnPaymentToTheStoreTitle;
                string EnAmountOfOnlinePaymentTitle = context.Request["EnAmountOfOnlinePaymentTitle"];
                ass.Settings["EnAmountOfOnlinePaymentTitle"].Value = EnAmountOfOnlinePaymentTitle;
                string EnWeChatPayment = context.Request["EnWeChatPayment"];
                ass.Settings["EnWeChatPayment"].Value = EnWeChatPayment;
                string EnConfirmationOfPayment = context.Request["EnConfirmationOfPayment"];
                ass.Settings["EnConfirmationOfPayment"].Value = EnConfirmationOfPayment;
                string EnNameNotNull = context.Request["EnNameNotNull"];
                ass.Settings["EnNameNotNull"].Value = EnNameNotNull;
                string EnPhoneNotNull = context.Request["EnPhoneNotNull"];
                ass.Settings["EnPhoneNotNull"].Value = EnPhoneNotNull;
                string EnPhoneNotFormat = context.Request["EnPhoneNotFormat"];
                ass.Settings["EnPhoneNotFormat"].Value = EnPhoneNotFormat;
                string EnSingleFailure = context.Request["EnSingleFailure"];
                ass.Settings["EnSingleFailure"].Value = EnSingleFailure;
                string EnLowStocks = context.Request["EnLowStocks"];
                ass.Settings["EnLowStocks"].Value = EnLowStocks;
                string EnNoGoods = context.Request["EnNoGoods"];
                ass.Settings["EnNoGoods"].Value = EnNoGoods;



                string IndexSerach = context.Request["IndexSerach"];
                ass.Settings["IndexSerach"].Value = IndexSerach;
                string CurrentPopularity = context.Request["CurrentPopularity"];
                ass.Settings["CurrentPopularity"].Value = CurrentPopularity;
                string GroupPurchase = context.Request["GroupPurchase"];
                ass.Settings["GroupPurchase"].Value = GroupPurchase;
                string IndexTitle = context.Request["IndexTitle"];
                ass.Settings["IndexTitle"].Value = IndexTitle;
                string DetailTitle = context.Request["DetailTitle"];
                ass.Settings["DetailTitle"].Value = DetailTitle;
                string SalesVolume = context.Request["SalesVolume"];
                ass.Settings["SalesVolume"].Value = SalesVolume;
                string Merchant = context.Request["Merchant"];
                ass.Settings["Merchant"].Value = Merchant;
                string OnlinePayment = context.Request["OnlinePayment"];
                ass.Settings["OnlinePayment"].Value = OnlinePayment;
                string PaymentToTheStore = context.Request["PaymentToTheStore"];
                ass.Settings["PaymentToTheStore"].Value = PaymentToTheStore;
                string OriginalPrice = context.Request["OriginalPrice"];
                ass.Settings["OriginalPrice"].Value = OriginalPrice;
                string PresentPrice = context.Request["PresentPrice"];
                ass.Settings["PresentPrice"].Value = PresentPrice;
                string Discount = context.Request["Discount"];
                ass.Settings["Discount"].Value = Discount;
                string RushToBuy = context.Request["RushToBuy"];
                ass.Settings["RushToBuy"].Value = RushToBuy;
                string OrderTitle = context.Request["OrderTitle"];
                ass.Settings["OrderTitle"].Value = OrderTitle;
                string NonReservation = context.Request["NonReservation"];
                ass.Settings["NonReservation"].Value = NonReservation;
                string PayToTheShop = context.Request["PayToTheShop"];
                ass.Settings["PayToTheShop"].Value = PayToTheShop;
                string Number = context.Request["Number"];
                ass.Settings["Number"].Value = Number;
                string Name = context.Request["Name"];
                ass.Settings["Name"].Value = Name;
                string NameMsg = context.Request["NameMsg"];
                ass.Settings["NameMsg"].Value = NameMsg;
                string Phone = context.Request["Phone"];
                ass.Settings["Phone"].Value = Phone;
                string PhoneMsg = context.Request["PhoneMsg"];
                ass.Settings["PhoneMsg"].Value = PhoneMsg;
                string PaymentToTheStoreTitle = context.Request["PaymentToTheStoreTitle"];
                ass.Settings["PaymentToTheStoreTitle"].Value = PaymentToTheStoreTitle;
                string AmountOfOnlinePaymentTitle = context.Request["AmountOfOnlinePaymentTitle"];
                ass.Settings["AmountOfOnlinePaymentTitle"].Value = AmountOfOnlinePaymentTitle;
                string WeChatPayment = context.Request["WeChatPayment"];
                ass.Settings["WeChatPayment"].Value = WeChatPayment;
                string ConfirmationOfPayment = context.Request["ConfirmationOfPayment"];
                ass.Settings["ConfirmationOfPayment"].Value = ConfirmationOfPayment;
                string NameNotNull = context.Request["NameNotNull"];
                ass.Settings["NameNotNull"].Value = NameNotNull;
                string PhoneNotNull = context.Request["PhoneNotNull"];
                ass.Settings["PhoneNotNull"].Value = PhoneNotNull;
                string PhoneNotFormat = context.Request["PhoneNotFormat"];
                ass.Settings["PhoneNotFormat"].Value = PhoneNotFormat;
                string SingleFailure = context.Request["SingleFailure"];
                ass.Settings["SingleFailure"].Value = SingleFailure;
                string LowStocks = context.Request["LowStocks"];
                ass.Settings["LowStocks"].Value = LowStocks;
                string NoGoods = context.Request["NoGoods"];
                ass.Settings["NoGoods"].Value = NoGoods;
                string EnYuan = context.Request["EnYuan"];
                ass.Settings["EnYuan"].Value = EnYuan;

                string Yuan = context.Request["Yuan"];
                ass.Settings["Yuan"].Value = Yuan;


                cfg.Save();

                result.result = true;
                result.message = "";
                return JsonConvert.SerializeObject(result);

            }
            catch (Exception ex)
            {
                new GrouponDAL.product().AddLog("editSys:" + ex.Message);
                result.result = false;
                result.message = ex.Message;
                return JsonConvert.SerializeObject(result);
            }
        }

        private string setCookie(HttpContext context)
        {
            string key = context.Request["key"];
            JsonResult result = new JsonResult();

            try
            {
                HttpCookie admbmCookie = new HttpCookie("LanguageType");
                admbmCookie.Value = key;
                admbmCookie.Expires = DateTime.Now.AddMonths(1);
                context.Response.AppendCookie(admbmCookie);

                result.result = true;
                result.message = "";
                return JsonConvert.SerializeObject(result);
            }
            catch (Exception ex)
            {
                result.result = false;
                result.message = "服务器繁忙，请稍后重试！";
                return JsonConvert.SerializeObject(result);
            }

        }

        public class JsonResult
        {
            public bool result { get; set; }
            public string message { get; set; }
            public string data { get; set; }
            public int flage { get; set; }
        }

        public bool IsReusable
        {
            get
            {
                return false;
            }
        }

        private string findProduct(HttpContext context)
        {
            string key = context.Request["key"];
            string[] c_type = context.Request["c_type"].Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries);
            string[] c_area = context.Request["c_area"].Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries);
            string[] c_num = context.Request["c_num"].Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries);
            string c_sort = context.Request["c_sort"];
            JsonResult result = new JsonResult();
            GrouponDAL.product dal = new GrouponDAL.product();


            int sort = -1;

            int.TryParse(c_sort, out sort);

            //获取中英文标识
            int LanguageType = 0;
            if (HttpContext.Current.Request.Cookies["LanguageType"] != null)
            {
                int.TryParse(HttpContext.Current.Request.Cookies["LanguageType"].Value, out LanguageType);
            }

            Configuration cfg = System.Web.Configuration.WebConfigurationManager.OpenWebConfiguration(System.Web.HttpContext.Current.Request.ApplicationPath);
            AppSettingsSection ass = cfg.AppSettings;
            string EnNoGoods = "";
            if (LanguageType != 0)
            {
                EnNoGoods = ass.Settings["EnNoGoods"].Value;
            }
            else
            {
                EnNoGoods = ass.Settings["NoGoods"].Value;
            }

            List<GrouponModel.product> list = dal.GetListByKey(c_area, c_type, c_num, sort, key, LanguageType);
            if (list.Count > 0)
            {
                result.result = true;
                result.message = "";
                result.data = Newtonsoft.Json.JsonConvert.SerializeObject(list);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = true;
                result.message = EnNoGoods;
                return JsonConvert.SerializeObject(result);
            }

        }

        //private string CheckPayment(HttpContext context)
        //{
        //    string URL = context.Request.Url.Scheme + "://" + context.Request.Url.Authority;
        //    JsonResult result = new JsonResult();
        //    PaylinxCheckPayment checkPay = new PaylinxCheckPayment();
        //    string platform = context.Request["platform"].ToString();
        //    string out_trade_no = context.Request["tradeNo"].ToString();
        //    bool resultStr = false;

        //    resultStr = checkPay.CheckTransactionFromDB(out_trade_no);

        //    if (resultStr)
        //    {
        //        result.result = resultStr;
        //        //result.data = "https://www.sina.com.cn/";
        //        result.data = URL + "/P.aspx?UID=" + out_trade_no;
        //    }
        //    else
        //    {
        //        result.result = resultStr;
        //        result.data = URL;
        //    }

        //    return JsonConvert.SerializeObject(result);
        //}

        private string ImportProduct(HttpContext context)
        {
            string ProductString= context.Request["ProductList"];
            JsonResult result = new JsonResult();
            GrouponDAL.product dal = new GrouponDAL.product();
            List<GrouponModel.product> ProductList = new List<GrouponModel.product>();

            DataTable dt = JsonConvert.DeserializeObject<DataTable>(ProductString);
            foreach (DataRow item in dt.Rows)
            {
                GrouponModel.product product = new GrouponModel.product();
                product.RestaurantName = "【袋鼠生鲜】";
                product.RestaurantAddress = "city";
                product.BusinessCircle = 1;
                product.RestaurantPhone = "0452540718";
                product.BossPhone = "0452540718";
                product.ProductName = string.Format("{0}-{1}（{2} {3}）"
                    , item["CODE"], item["ProductName1"], item["ProductName2"], item["Units"]);
                product.NowPrice = Convert.ToDecimal(item["NowPrice"]);
                product.OriginalPrice = Convert.ToDecimal(item["NowPrice"]);

                product.ProductQuantity = Convert.ToInt32(item["CurrentQuantity"]);
                product.CurrentQuantity = Convert.ToInt32(item["CurrentQuantity"]);
                product.ProductType = 95;
                if (product.CurrentQuantity == 0)
                {
                    product.IsDelete = 1;
                }

                product.OnlinePaymentRatio = 100;
                product.IsSendMessageUser = "1";
                product.SortNum = 1;
                product.mealsId = 1;
                product.ProductTimeliness = 24;
                ProductList.Add(product);
            }

            int rows = dal.addGrouply(ProductList);

            if ( rows > 0)
            {
                result.result = true;
                result.message = string.Format("导入{0}行产品成功", rows);
                return JsonConvert.SerializeObject(result);
            }
            else
            {
                result.result = false;
                result.message = "导入失败";
                return JsonConvert.SerializeObject(result);
            }

        }
    }
}