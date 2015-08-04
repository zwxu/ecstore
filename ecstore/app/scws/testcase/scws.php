<?php

$segmentObj = search_core::segment();
$segmentObj->pre_filter(new search_service_filter_cjk);
$segmentObj->token_filter(new search_service_filter_lowercase);

$segmentObj->set('一项研究发现，每天服用小剂量阿司匹林可以显著的降低癌症发病率。 英国研究人员发现，在至少五年时间里每天服用小剂量阿司匹林（75毫克），能减少癌症发病率10%到60%，不同类型的癌症效果略有差异。研究人员调查了超过25,500位患者，他们参与了低剂量阿司匹林预防心血管疾病的潜力的实验。结果发现，长期服用低剂量阿司匹林似乎减少了三分之一的结直肠癌死亡风险，服用阿司匹林的胃肠道癌症患者死亡率下降54%，食管癌患者下降60%。这是首次证明阿司匹林能减少癌症死亡风险，但研究主要作者表示，结论并不意味着成年人应该立即开始服用阿司匹林。报告发表在12月7日的《柳叶刀》杂志上。EDwin', 'utf-8');

while($row = $segmentObj->next()){
    $res[] = $row;
}

print_r($res);exit;
