/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';
import { cloneDeep } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlSelect,
	AtrcNestedObjUpdateByKey2,
	AtrcControlCheckbox,
	AtrcControlSortable,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { dashboard = {} } = dbSettings;

	const { sidebarContent = {} } = dashboard;

	return (
		<AtrcContent className={classNames(AtrcPrefix('cont-dashboard'))}>
			<AtrcPanelRow className={classNames('at-flx-col', 'at-al-itm-st')}>
				<AtrcControlSortable
					label={__(
						'Enable, disable and arrange sidebar content',
						'brand-master'
					)}
					onChange={(newVal) => {
						const updatedSettings = AtrcNestedObjUpdateByKey2({
							settings: dashboard,
							key1: 'sidebarContent',
							key2: 'sort',
							val2: newVal,
						});
						dbUpdateSetting('dashboard', updatedSettings);
					}}
					value={sidebarContent.sort}
					items={[
						{
							value: 'siteIdentity',
							children: (
								<AtrcControlCheckbox
									label={__('Site identity', 'brand-master')}
									checked={sidebarContent.siteIdentity}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: dashboard,
											key1: 'sidebarContent',
											key2: 'siteIdentity',
											val2: newVal,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
								/>
							),
						},
						{
							value: 'menu',
							children: (
								<AtrcControlCheckbox
									label={__('Menu', 'brand-master')}
									checked={sidebarContent.menu}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: dashboard,
											key1: 'sidebarContent',
											key2: 'menu',
											val2: newVal,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
								/>
							),
						},
						{
							value: 'social',
							children: (
								<AtrcControlCheckbox
									label={__('Social', 'brand-master')}
									checked={sidebarContent.social}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: dashboard,
											key1: 'sidebarContent',
											key2: 'social',
											val2: newVal,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
								/>
							),
						},
						{
							value: 'userInfo',
							children: (
								<AtrcControlCheckbox
									label={__('Login user info', 'brand-master')}
									checked={sidebarContent.userInfo}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: dashboard,
											key1: 'sidebarContent',
											key2: 'userInfo',
											val2: newVal,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
								/>
							),
						},
						{
							value: 'logout',
							children: (
								<AtrcControlCheckbox
									label={__('Logout', 'brand-master')}
									checked={sidebarContent.logout}
									onChange={(newVal) => {
										const updatedSettings = AtrcNestedObjUpdateByKey2({
											settings: dashboard,
											key1: 'sidebarContent',
											key2: 'logout',
											val2: newVal,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
								/>
							),
						},
					]}
				/>
			</AtrcPanelRow>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlSelect
					label={__('Select separation element', 'brand-master')}
					value={sidebarContent.sepEl}
					options={[
						{
							label: __('Select', 'brand-master'),
							value: '',
						},
						{
							label: __('Site identity', 'brand-master'),
							value: 'siteIdentity',
						},
						{
							label: __('Menu', 'brand-master'),
							value: 'menu',
						},
						{
							label: __('Social', 'brand-master'),
							value: 'social',
						},
						{
							label: __('Login user info', 'brand-master'),
							value: 'userInfo',
						},
						{
							label: __('Logout', 'brand-master'),
							value: 'logout',
						},
					]}
					onChange={(newVal) => {
						const updatedSettings = AtrcNestedObjUpdateByKey2({
							settings: dashboard,
							key1: 'sidebarContent',
							key2: 'sepEl',
							val2: newVal,
						});
						dbUpdateSetting('dashboard', updatedSettings);
					}}
				/>
			</AtrcPanelRow>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmdSdDocs1 = !localStorageClone.bmdSdDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__('What is sidebar content?', 'brand-master')}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								"Sidebar content is prominently displayed at the side of the Frontend dashboard, serving as a focal point for your dashboard's visual presentation. You have the flexibility to incorporate all available elements into the sidebar content, giving you the ability to enable, disable, and seamlessly sort these elements according to your preferences. This feature empowers you to create a personalized and visually engaging dashboard sidebar tailored to your specific needs.",
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
					<AtrcPanelBody
						title={__('What is the separation element?', 'brand-master')}
						initialOpen={false}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'The separation element serves as the focal point from which other elements diverge, creating a distinct visual separation to the top and bottom. Rather than acting as a center element, it functions as the origin of the layout, determining the arrangement of elements for an organized and visually appealing Frontend dashboard.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmdSdDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('Frontend Dashboard settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
						className: 'at-p',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmdSdDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmdSdDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
